<?php

namespace App\Services;

use App\Http\Requests\Admin\Ebook\StoreEbookAccessCodeRequest;
use App\Models\EbookAccessCode;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Throwable;

class EbookAccessCodeService
{
    private const IMPORT_HEADER = 'Access Codes';

    public function getCodesForProduct(Product $product): Collection
    {
        return $product->ebookAccessCodes()->with(['order', 'orderItem'])->latest()->get();
    }

    public function addCodes(Product $product, array $codes): array
    {
        return DB::transaction(function () use ($product, $codes) {
            $added = [];
            foreach ($codes as $code) {
                $codeTrimmed = trim((string) $code);
                if ($codeTrimmed === '') {
                    continue;
                }
                if (EbookAccessCode::where('code', $codeTrimmed)->exists()) {
                    throw ValidationException::withMessages([
                        'codes' => [__('messages.ebook.code_exists')],
                    ]);
                }
                $added[] = EbookAccessCode::create([
                    'product_id' => $product->id,
                    'code' => $codeTrimmed,
                ]);
            }
            return $added;
        });
    }

    public function importCodes(Product $product, UploadedFile $file): array
    {
        if (! $product->is_e_copy) {
            throw ValidationException::withMessages([
                'product' => [__('messages.ebook.not_ebook_product')],
            ]);
        }

        $summary = [
            'total_rows' => 0,
            'imported_codes' => 0,
            'rejected_codes' => 0,
            'errors' => [],
        ];
        $seenCodes = [];
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            $this->importCsv($product, $file->getRealPath(), $summary, $seenCodes);
        } elseif ($extension === 'xlsx') {
            $this->importXlsx($product, $file->getRealPath(), $summary, $seenCodes);
        } else {
            throw ValidationException::withMessages([
                'file' => [__('messages.ebook.invalid_import_file')],
            ]);
        }

        if ($summary['imported_codes'] > 0) {
            $this->syncProductStock($product);
        }

        return $summary;
    }

    public function createImportTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Access Codes');
        $sheet->setCellValue('A1', self::IMPORT_HEADER);
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->freezePane('A2');

        return $spreadsheet;
    }

    public function deleteCode(EbookAccessCode $ebookAccessCode): void
    {
        if ($ebookAccessCode->used_at) {
            throw ValidationException::withMessages([
                'code' => [__('messages.ebook.cannot_delete_used')],
            ]);
        }
        $ebookAccessCode->delete();
    }

    public function getAvailableCount(Product $product): int
    {
        return EbookAccessCode::where('product_id', $product->id)
            ->whereNull('used_at')
            ->count();
    }

    private function importCsv(Product $product, string $path, array &$summary, array &$seenCodes): void
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => [__('messages.ebook.invalid_import_file')],
            ]);
        }

        $header = fgetcsv($handle, null, ',', '"', '');
        if (is_array($header) && isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        }
        if ($header !== [self::IMPORT_HEADER]) {
            fclose($handle);
            throw ValidationException::withMessages([
                'file' => [__('messages.ebook.invalid_import_header')],
            ]);
        }

        $rowNumber = 1;
        $chunk = [];
        while (($values = fgetcsv($handle, null, ',', '"', '')) !== false) {
            $rowNumber++;
            $chunk[] = ['row_number' => $rowNumber, 'values' => $values];
            if (count($chunk) === 500) {
                $this->processImportChunk($product, $chunk, $summary, $seenCodes);
                $chunk = [];
            }
        }
        fclose($handle);

        if ($chunk !== []) {
            $this->processImportChunk($product, $chunk, $summary, $seenCodes);
        }
    }

    private function importXlsx(Product $product, string $path, array &$summary, array &$seenCodes): void
    {
        try {
            $reader = new Xlsx;
            $reader->setReadDataOnly(true);
            $worksheets = $reader->listWorksheetInfo($path);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'file' => [__('messages.ebook.invalid_import_file')],
            ]);
        }

        if ($worksheets === [] || ($worksheets[0]['totalColumns'] ?? 1) !== 1) {
            throw ValidationException::withMessages([
                'file' => [__('messages.ebook.invalid_import_header')],
            ]);
        }

        $totalRows = (int) ($worksheets[0]['totalRows'] ?? 0);
        $readFilter = new EbookAccessCodeImportReadFilter;
        $reader->setReadFilter($readFilter);
        $readFilter->setRows(1, 1);
        try {
            $workbook = $reader->load($path);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'file' => [__('messages.ebook.invalid_import_file')],
            ]);
        }
        $header = $workbook->getSheet(0)->getCell('A1')->getValue();
        $workbook->disconnectWorksheets();
        if ($header !== self::IMPORT_HEADER) {
            throw ValidationException::withMessages([
                'file' => [__('messages.ebook.invalid_import_header')],
            ]);
        }

        for ($startRow = 2; $startRow <= $totalRows; $startRow += 500) {
            $endRow = min($startRow + 499, $totalRows);
            $readFilter->setRows($startRow, $endRow);
            try {
                $workbook = $reader->load($path);
            } catch (Throwable) {
                throw ValidationException::withMessages([
                    'file' => [__('messages.ebook.invalid_import_file')],
                ]);
            }

            $sheet = $workbook->getSheet(0);
            $chunk = [];
            for ($row = $startRow; $row <= $endRow; $row++) {
                $chunk[] = [
                    'row_number' => $row,
                    'values' => [$sheet->getCell('A'.$row)->getValue()],
                ];
            }
            $workbook->disconnectWorksheets();
            $this->processImportChunk($product, $chunk, $summary, $seenCodes);
        }
    }

    private function processImportChunk(Product $product, array $rows, array &$summary, array &$seenCodes): void
    {
        $candidates = [];
        foreach ($rows as $row) {
            $summary['total_rows']++;
            if (count($row['values']) !== 1) {
                $this->rejectImportRow($summary, $row['row_number'], __('messages.ebook.one_code_per_row'));

                continue;
            }

            $rawCode = $row['values'][0] ?? null;
            $code = is_string($rawCode) ? trim($rawCode) : $rawCode;
            $validator = Validator::make(
                ['code' => $code],
                ['code' => StoreEbookAccessCodeRequest::codeRules()],
                [
                    'code.required' => __('messages.ebook.code_required'),
                    'code.string' => __('messages.ebook.code_must_be_text'),
                    'code.max' => __('messages.ebook.code_too_long'),
                ]
            );
            if ($validator->fails()) {
                $this->rejectImportRow($summary, $row['row_number'], $validator->errors()->first());

                continue;
            }

            if (isset($seenCodes[$code])) {
                $this->rejectImportRow($summary, $row['row_number'], __('messages.ebook.duplicate_code_in_file'));

                continue;
            }
            $seenCodes[$code] = true;
            $candidates[] = ['row_number' => $row['row_number'], 'code' => $code];
        }

        if ($candidates === []) {
            return;
        }

        $existingCodes = EbookAccessCode::query()
            ->whereIn('code', array_column($candidates, 'code'))
            ->pluck('code')
            ->flip();

        foreach ($candidates as $candidate) {
            if (isset($existingCodes[$candidate['code']])) {
                $this->rejectImportRow($summary, $candidate['row_number'], __('messages.ebook.code_exists'));

                continue;
            }

            $now = now();
            $inserted = DB::table('ebook_access_codes')->insertOrIgnore([
                'product_id' => $product->id,
                'code' => $candidate['code'],
                'used_at' => null,
                'order_id' => null,
                'order_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($inserted === 1) {
                $summary['imported_codes']++;
            } else {
                $this->rejectImportRow($summary, $candidate['row_number'], __('messages.ebook.code_exists'));
            }
        }
    }

    private function rejectImportRow(array &$summary, int $rowNumber, string $message): void
    {
        $summary['rejected_codes']++;
        $summary['errors'][] = [
            'row_number' => $rowNumber,
            'message' => __('messages.ebook.import_row_error', [
                'row' => $rowNumber,
                'reason' => $message,
            ]),
        ];
    }

    private function syncProductStock(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $lockedProduct->stock = $this->getAvailableCount($lockedProduct);
            $lockedProduct->save();
        });
    }

    public function reserveCodes(Product $product, int $quantity, int $orderId): array
    {
        return DB::transaction(function () use ($product, $quantity, $orderId) {
            $available = EbookAccessCode::where('product_id', $product->id)
                ->whereNull('used_at')
                ->lockForUpdate()
                ->limit($quantity)
                ->get();

            if ($available->count() < $quantity) {
                throw ValidationException::withMessages([
                    'cart' => [__('messages.ebook.insufficient_codes')],
                ]);
            }

            $reserved = [];
            foreach ($available as $code) {
                $code->update([
                    'used_at' => now(),
                    'order_id' => $orderId,
                ]);
                $reserved[] = $code;
            }

            // Keep product stock in sync for compatibility
            $product->stock = $this->getAvailableCount($product);
            $product->save();

            return $reserved;
        });
    }

    public function releaseCodesForOrder(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $codes = EbookAccessCode::where('order_id', $orderId)->lockForUpdate()->get();
            $productId = null;
            foreach ($codes as $code) {
                $productId = $productId ?? $code->product_id;
                $code->update([
                    'used_at' => null,
                    'order_id' => null,
                    'order_item_id' => null,
                ]);
            }
            if (! empty($productId)) {
                $product = Product::find($productId);
                if ($product) {
                    $product->stock = $this->getAvailableCount($product);
                    $product->save();
                }
            }
        });
    }

    public function attachCodesToOrderItem($order, $orderItem, array $codes): void
    {
        foreach ($codes as $code) {
            $code->update([
                'order_item_id' => $orderItem->id,
            ]);
            \App\Models\OrderEbookCode::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'ebook_access_code_id' => $code->id,
            ]);
        }
    }
}

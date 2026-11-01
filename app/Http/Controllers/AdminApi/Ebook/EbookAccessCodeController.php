<?php

namespace App\Http\Controllers\AdminApi\Ebook;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ebook\ImportEbookAccessCodesRequest;
use App\Http\Requests\Admin\Ebook\StoreEbookAccessCodeRequest;
use App\Models\EbookAccessCode;
use App\Models\Product;
use App\Services\EbookAccessCodeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EbookAccessCodeController extends Controller
{
    use ApiResponse;

    public function __construct(
        private EbookAccessCodeService $ebookAccessCodeService
    ) {}

    public function index(Product $product): JsonResponse
    {
        if (! $product->is_e_copy) {
            return $this->error(__('messages.ebook.not_ebook_product'), 422);
        }

        $codes = $this->ebookAccessCodeService->getCodesForProduct($product);

        return $this->success([
            'codes' => $codes->map(function ($code) {
                return [
                    'id' => $code->id,
                    'code' => $code->code,
                    'used_at' => $code->used_at?->toDateTimeString(),
                    'order_id' => $code->order_id,
                    'order_item_id' => $code->order_item_id,
                    'created_at' => $code->created_at?->toDateTimeString(),
                ];
            }),
            'available_count' => $this->ebookAccessCodeService->getAvailableCount($product),
        ], 'messages.ebook.codes_retrieved');
    }

    public function store(StoreEbookAccessCodeRequest $request, Product $product): JsonResponse
    {
        if (! $product->is_e_copy) {
            return $this->error(__('messages.ebook.not_ebook_product'), 422);
        }

        try {
            $added = $this->ebookAccessCodeService->addCodes($product, $request->validated('codes'));
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? __('messages.ebook.code_exists');
            return $this->error($message, 422);
        }

        return $this->success([
            'added_count' => count($added),
        ], 'messages.ebook.codes_added', 201);
    }

    public function import(ImportEbookAccessCodesRequest $request, Product $product): JsonResponse
    {
        if (! $product->is_e_copy) {
            return $this->error(__('messages.ebook.not_ebook_product'), 422);
        }

        $summary = $this->ebookAccessCodeService->importCodes($product, $request->file('file'));

        return $this->success($summary, 'messages.ebook.codes_imported');
    }

    public function importTemplate(): JsonResponse|StreamedResponse
    {

        $spreadsheet = $this->ebookAccessCodeService->createImportTemplate();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'ebook-access-codes-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function destroy(EbookAccessCode $ebookAccessCode): JsonResponse
    {
        try {
            $this->ebookAccessCodeService->deleteCode($ebookAccessCode);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? __('messages.ebook.cannot_delete_used');
            return $this->error($message, 422);
        }

        return $this->success(null, 'messages.ebook.code_deleted');
    }
}

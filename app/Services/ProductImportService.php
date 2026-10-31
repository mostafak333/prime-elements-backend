<?php

namespace App\Services;

use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Models\Category;
use App\Models\ProductDetail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class ProductImportService
{
    private const MAX_DATA_ROWS = 5000;

    private const COLUMNS = [
        'category' => 'Category*',
        'title_en' => 'Title (English)',
        'title_ar' => 'Title (Arabic)',
        'name_en' => 'Name (English)*',
        'name_ar' => 'Name (Arabic)*',
        'short_description_en' => 'Short Description (English)*',
        'short_description_ar' => 'Short Description (Arabic)*',
        'price' => 'Price*',
        'discount_percentage' => 'Discount Percentage',
        'discount_start_at' => 'Discount Start At',
        'discount_end_at' => 'Discount End At',
        'stock' => 'Stock',
        'status' => 'Status*',
        'is_new_arrival' => 'New Arrival*',
        'is_best_seller' => 'Best Seller*',
        'is_e_copy' => 'E-Book*',
        'ebook_url' => 'E-Book URL',
        'publisher' => 'Publisher*',
        'author' => 'Author',
        'detail_publisher' => 'Detail Publisher',
        'language' => 'Language',
        'pages' => 'Pages',
        'isbn' => 'ISBN',
        'publication_date' => 'Publication Date',
        'description_en' => 'Description (English)',
        'description_ar' => 'Description (Arabic)',
    ];

    public function __construct(
        protected ProductService $productService
    ) {}

    public function createTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $instructions = $spreadsheet->getActiveSheet();
        $instructions->setTitle('Instructions');
        $instructions->fromArray([
            ['Books bulk import'],
            ['Complete one book per row in the Books sheet. Keep the header row unchanged.'],
            ['Columns marked * are required. Discount dates use YYYY-MM-DD and must be in chronological order. Date validation does not guarantee a calendar popup.'],
            ['Choose exactly one Category from its dropdown. The list is generated from existing categories.'],
            ['Choose Enabled or Disabled for Status, New Arrival, Best Seller, and E-Book.'],
            ['For an e-book, set E-Book to TRUE and provide an E-Book URL. Stock must be blank or zero.'],
            ['Title (English) and Title (Arabic) are plain text fields and are not linked to the Titles table. ISBN duplicates are rejected when an ISBN is supplied.'],
            ['Author and Publisher are text fields in this project; they are not related database records.'],
            ['The workbook accepts up to 5000 non-empty book rows. Images are not included in spreadsheet import.'],
        ], null, 'A1');
        $instructions->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $instructions->getColumnDimension('A')->setWidth(120);
        $instructions->getStyle('A1:A9')->getAlignment()->setWrapText(true);

        $books = $spreadsheet->createSheet();
        $books->setTitle('Books');
        $books->fromArray([array_values(self::COLUMNS)], null, 'A1');
        $lastColumn = Coordinate::stringFromColumnIndex(count(self::COLUMNS));
        $books->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $books->getStyle("A1:{$lastColumn}1")->getAlignment()->setWrapText(true);
        $books->getRowDimension(1)->setRowHeight(32);
        $books->freezePane('A2');
        $books->setAutoFilter("A1:{$lastColumn}".(self::MAX_DATA_ROWS + 1));

        foreach (range(1, count(self::COLUMNS)) as $column) {
            $books->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        }
        $isbnColumn = Coordinate::stringFromColumnIndex(array_search('ISBN', array_values(self::COLUMNS), true) + 1);
        $books->getStyle($isbnColumn.'2:'.$isbnColumn.(self::MAX_DATA_ROWS + 1))
            ->getNumberFormat()
            ->setFormatCode('@');

        $categories = Category::with('title:id,name_en,name_ar')
            ->orderBy('name_en')
            ->orderBy('name_ar')
            ->get();
        $categoryOptions = $this->categoryOptions($categories);

        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle('_Lists');
        $listSheet->setCellValue('A1', 'Categories');
        $listSheet->setCellValue('B1', 'BooleanOptions');
        foreach (array_values($categoryOptions) as $index => $label) {
            $listSheet->setCellValue('A'.($index + 2), $label);
        }
        $listSheet->fromArray([['Enabled'], ['Disabled']], null, 'B2');
        $lastListRow = max(2, count($categoryOptions) + 1);
        $spreadsheet->addNamedRange(new NamedRange(
            'CategoryOptions',
            $listSheet,
            '$A$2:$A$'.$lastListRow
        ));
        $spreadsheet->addNamedRange(new NamedRange(
            'BooleanOptions',
            $listSheet,
            '$B$2:$B$3'
        ));
        $listSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $categoryValidation = $this->listValidation(
            '=CategoryOptions',
            'Select a category',
            'Choose one existing category from the dropdown.',
            'Invalid category',
            'Select one category from the dropdown list.'
        );
        $books->setDataValidation('A2:A'.(self::MAX_DATA_ROWS + 1), $categoryValidation);

        foreach (['status', 'is_new_arrival', 'is_best_seller', 'is_e_copy'] as $field) {
            $column = Coordinate::stringFromColumnIndex(array_search($field, array_keys(self::COLUMNS), true) + 1);
            $booleanValidation = $this->listValidation(
                '=BooleanOptions',
                'Select a value',
                'Choose Enabled or Disabled.',
                'Invalid value',
                'Choose Enabled or Disabled.'
            );
            $books->setDataValidation($column.'2:'.$column.(self::MAX_DATA_ROWS + 1), $booleanValidation);
        }

        foreach (['discount_start_at', 'discount_end_at'] as $field) {
            $column = Coordinate::stringFromColumnIndex(array_search($field, array_keys(self::COLUMNS), true) + 1);
            $books->getStyle($column.'2:'.$column.(self::MAX_DATA_ROWS + 1))
                ->getNumberFormat()
                ->setFormatCode('yyyy-mm-dd');

            $validation = new DataValidation;
            $validation->setType(DataValidation::TYPE_DATE);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setOperator(DataValidation::OPERATOR_BETWEEN);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setFormula1('DATE(1900,1,1)');
            $validation->setFormula2('DATE(9999,12,31)');
            $validation->setPromptTitle('Enter a date');
            $validation->setPrompt('Use YYYY-MM-DD.');
            $validation->setErrorTitle('Invalid date');
            $validation->setError('Enter a valid date in YYYY-MM-DD format.');
            $books->setDataValidation($column.'2:'.$column.(self::MAX_DATA_ROWS + 1), $validation);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function import(UploadedFile $file): array
    {
        try {
            $spreadsheet = (new Xlsx)->load($file->getRealPath());
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'file' => [__('messages.product_import.invalid_file')],
            ]);
        }

        $sheet = $spreadsheet->getSheetByName('Books');
        if ($sheet === null) {
            throw ValidationException::withMessages([
                'file' => [__('messages.product_import.missing_books_sheet')],
            ]);
        }

        $headers = array_values(self::COLUMNS);
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $actualHeaders = $sheet->rangeToArray("A1:{$lastColumn}1", null, true, false, false)[0];
        if ($actualHeaders !== $headers) {
            throw ValidationException::withMessages([
                'file' => [__('messages.product_import.invalid_headers')],
            ]);
        }

        $categories = Category::with('title:id,name_en,name_ar')
            ->orderBy('name_en')
            ->orderBy('name_ar')
            ->get();
        $categoryOptions = $this->categoryOptions($categories);
        $categoryIds = array_flip($categoryOptions);
        $rules = StoreProductRequest::productRules();
        $summary = [
            'total_rows' => 0,
            'successfully_imported' => 0,
            'failed_row_count' => 0,
            'failed_rows' => [],
        ];

        for ($row = 2; $row <= $sheet->getHighestRow(); $row++) {
            $values = [];
            foreach (array_keys(self::COLUMNS) as $index => $key) {
                $values[$key] = $this->readCellValue($sheet, $index + 1, $row, $key);
            }

            if (collect($values)->every(fn ($value) => $this->isBlank($value))) {
                continue;
            }

            $summary['total_rows']++;
            if ($summary['total_rows'] > self::MAX_DATA_ROWS) {
                throw ValidationException::withMessages([
                    'file' => [__('messages.product_import.too_many_rows', ['max' => self::MAX_DATA_ROWS])],
                ]);
            }

            $categoryLabel = trim((string) ($values['category'] ?? ''));
            $categoryId = $categoryIds[$categoryLabel] ?? ($categoryLabel === '' ? null : 0);
            $data = $this->productData($values, $categoryId);
            $validator = Validator::make($data, $rules, [
                'category_id.exists' => __('messages.product_import.invalid_category', ['category' => $categoryLabel]),
                'status.boolean' => __('messages.product_import.invalid_boolean'),
                'is_new_arrival.boolean' => __('messages.product_import.invalid_boolean'),
                'is_best_seller.boolean' => __('messages.product_import.invalid_boolean'),
                'is_e_copy.boolean' => __('messages.product_import.invalid_boolean'),
                'discount_start_at.date' => __('messages.product_import.invalid_discount_start_date'),
                'discount_end_at.date' => __('messages.product_import.invalid_discount_end_date'),
            ]);
            $validator->after(function ($validator) use ($data) {
                if (! empty($data['is_e_copy'])) {
                    if (empty($data['ebook_url'])) {
                        $validator->errors()->add('ebook_url', __('validation.custom.ebook_url.required'));
                    }
                    if (($data['stock'] ?? null) !== null && (int) $data['stock'] !== 0) {
                        $validator->errors()->add('stock', __('messages.ebook.cannot_set_stock_for_ebook'));
                    }
                }

                $startAt = $data['discount_start_at'] ?? null;
                $endAt = $data['discount_end_at'] ?? null;
                if ($startAt !== null && $endAt !== null) {
                    $startTimestamp = strtotime((string) $startAt);
                    $endTimestamp = strtotime((string) $endAt);
                    if ($startTimestamp !== false && $endTimestamp !== false && $endTimestamp < $startTimestamp) {
                        $validator->errors()->add(
                            'discount_end_at',
                            __('messages.product_import.discount_end_before_start')
                        );
                    }
                }
            });

            if ($validator->fails()) {
                $this->addFailedRow($summary, $row, $validator->errors()->all());

                continue;
            }

            $isbn = trim((string) ($data['detail']['isbn'] ?? ''));
            if ($isbn !== '' && ProductDetail::query()
                ->where('isbn', $isbn)
                ->whereHas('product')
                ->exists()) {
                $this->addFailedRow($summary, $row, [__('messages.product_import.duplicate_isbn', ['isbn' => $isbn])]);

                continue;
            }

            try {
                $this->productService->create($data);
                $summary['successfully_imported']++;
            } catch (Throwable $exception) {
                Log::error('Bulk book import row failed.', [
                    'row' => $row,
                    'exception' => $exception->getMessage(),
                ]);
                $this->addFailedRow($summary, $row, [__('messages.product_import.create_failed')]);
            }
        }

        $spreadsheet->disconnectWorksheets();

        return $summary;
    }

    private function productData(array $values, ?int $categoryId): array
    {
        $boolean = fn ($key) => $this->normalizeBoolean($values[$key] ?? null);
        $optional = fn ($key) => $this->emptyStringToNull($values[$key] ?? null);

        return [
            'category_id' => $categoryId,
            'name_en' => $optional('name_en'),
            'name_ar' => $optional('name_ar'),
            'short_description_en' => $optional('short_description_en'),
            'short_description_ar' => $optional('short_description_ar'),
            'price' => $optional('price'),
            'discount_percentage' => $optional('discount_percentage'),
            'discount_start_at' => $optional('discount_start_at'),
            'discount_end_at' => $optional('discount_end_at'),
            'stock' => $this->normalizeInteger($optional('stock')),
            'status' => $boolean('status'),
            'is_new_arrival' => $boolean('is_new_arrival'),
            'is_best_seller' => $boolean('is_best_seller'),
            'is_e_copy' => $boolean('is_e_copy'),
            'ebook_url' => $optional('ebook_url'),
            'publisher' => $optional('publisher'),
            'detail' => [
                'title_en' => $optional('title_en'),
                'title_ar' => $optional('title_ar'),
                'author' => $optional('author'),
                'publisher' => $optional('detail_publisher'),
                'language' => $optional('language'),
                'pages' => $this->normalizeInteger($optional('pages')),
                'isbn' => $optional('isbn'),
                'publication_date' => $optional('publication_date'),
                'description_en' => $optional('description_en'),
                'description_ar' => $optional('description_ar'),
            ],
        ];
    }

    private function categoryOptions($categories): array
    {
        $baseLabels = $categories->map(fn (Category $category) => trim($category->name_en.' / '.$category->name_ar));
        $counts = $baseLabels->countBy();
        $options = [];
        $usedLabels = [];

        foreach ($categories as $index => $category) {
            $label = $baseLabels[$index];
            if ($counts[$label] > 1) {
                $label .= ' ['.($category->slug ?: 'category').']';
            }
            $suffix = 2;
            $uniqueLabel = $label;
            while (isset($usedLabels[$uniqueLabel])) {
                $uniqueLabel = $label.' ('.$suffix++.')';
            }
            $usedLabels[$uniqueLabel] = true;
            $options[$category->id] = $uniqueLabel;
        }

        return $options;
    }

    private function listValidation(
        string $formula,
        string $promptTitle,
        string $prompt,
        string $errorTitle,
        string $error
    ): DataValidation {
        $validation = new DataValidation;
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setPromptTitle($promptTitle);
        $validation->setPrompt($prompt);
        $validation->setErrorTitle($errorTitle);
        $validation->setError($error);
        $validation->setFormula1($formula);

        return $validation;
    }

    private function readCellValue(Worksheet $sheet, int $column, int $row, string $key): mixed
    {
        $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($column).$row);
        $value = $cell->getValue();
        if ($key === 'isbn' && is_numeric($value)) {
            return number_format((float) $value, 0, '.', '');
        }
        if (in_array($key, ['discount_start_at', 'discount_end_at', 'publication_date'], true)
            && is_numeric($value)
            && Date::isDateTime($cell)) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        return $value;
    }

    private function normalizeBoolean(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_string($value)) {
            return match (strtolower(trim($value))) {
                'enabled', 'true', 'yes' => 1,
                'disabled', 'false', 'no' => 0,
                default => $value,
            };
        }

        return $value;
    }

    private function normalizeInteger(mixed $value): mixed
    {
        if (is_numeric($value) && (float) $value === (float) (int) $value) {
            return (int) $value;
        }

        return $value;
    }

    private function emptyStringToNull(mixed $value): mixed
    {
        return is_string($value) && trim($value) === '' ? null : $value;
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }

    private function addFailedRow(array &$summary, int $row, array $errors): void
    {
        $summary['failed_row_count']++;
        $summary['failed_rows'][] = [
            'row_number' => $row,
            'errors' => array_map(fn ($error) => "Row {$row}: {$error}", $errors),
        ];
    }
}

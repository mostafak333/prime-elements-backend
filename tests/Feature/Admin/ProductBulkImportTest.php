<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Title;
use App\Services\ProductImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductBulkImportTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::create([
            'name' => 'Import Admin',
            'email' => 'import-admin@test.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);
        $this->token = JWTAuth::fromUser($admin);

        $title = Title::create(['name_en' => 'Test Title', 'name_ar' => 'عنوان']);
        $this->category = Category::create([
            'title_id' => $title->id,
            'name_en' => 'Books',
            'name_ar' => 'كتب',
            'slug' => 'books',
            'status' => true,
            'is_filter' => false,
        ]);
    }

    public function test_admin_can_import_a_book_and_store_bilingual_detail_titles(): void
    {
        $response = $this->importRows([$this->validRow()]);

        $response->assertOk()
            ->assertJsonPath('data.total_rows', 1)
            ->assertJsonPath('data.successfully_imported', 1)
            ->assertJsonPath('data.failed_row_count', 0);

        $product = Product::with('detail')->firstOrFail();
        $this->assertSame('English Detail Title', $product->detail->title_en);
        $this->assertSame('عنوان تفصيلي', $product->detail->title_ar);
        $this->assertSame('Sample Author', $product->detail->author);
    }

    public function test_existing_single_product_creation_still_accepts_bilingual_detail_titles(): void
    {
        $response = $this->postJson('/api/admin/products', [
            'category_id' => $this->category->id,
            'name_en' => 'Single API Book',
            'name_ar' => 'كتاب منفرد',
            'short_description_en' => 'English description.',
            'short_description_ar' => 'وصف باللغة العربية.',
            'price' => 20,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Single Publisher',
            'detail' => [
                'title_en' => 'Single English Title',
                'title_ar' => 'عنوان عربي منفرد',
            ],
        ], $this->authHeaders());

        $response->assertCreated();
        $this->assertDatabaseHas('product_details', [
            'title_en' => 'Single English Title',
            'title_ar' => 'عنوان عربي منفرد',
        ]);
    }

    public function test_missing_required_fields_are_reported_with_excel_row_number(): void
    {
        $row = $this->validRow();
        $row['Name (English)*'] = '';

        $response = $this->importRows([$row]);

        $response->assertOk()
            ->assertJsonPath('data.successfully_imported', 0)
            ->assertJsonPath('data.failed_row_count', 1);
        $errors = $response->json('data.failed_rows.0.errors');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Row 2:', implode(' ', $errors));
        $this->assertDatabaseCount('products', 0);
    }

    public function test_invalid_category_is_rejected_without_creating_a_product(): void
    {
        $row = $this->validRow();
        $row['Category*'] = 'Missing Category';

        $response = $this->importRows([$row]);

        $response->assertOk()->assertJsonPath('data.failed_row_count', 1);
        $this->assertStringContainsString(
            'Row 2: Category "Missing Category" does not match an existing category.',
            implode(' ', $response->json('data.failed_rows.0.errors'))
        );
        $this->assertDatabaseCount('products', 0);
    }

    public function test_duplicate_isbn_is_rejected(): void
    {
        $first = $this->validRow();
        $first['ISBN'] = '9780000000001';
        $second = $this->validRow();
        $second['Title (English)'] = 'Another Detail Title';
        $second['Name (English)*'] = 'Another Book';
        $second['ISBN'] = '9780000000001';

        $response = $this->importRows([$first, $second]);

        $response->assertOk()
            ->assertJsonPath('data.successfully_imported', 1)
            ->assertJsonPath('data.failed_row_count', 1)
            ->assertJsonPath('data.failed_rows.0.row_number', 3);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_enabled_and_disabled_values_map_to_product_booleans(): void
    {
        $row = $this->validRow();
        $row['Status*'] = 'Enabled';
        $row['New Arrival*'] = 'Disabled';
        $row['Best Seller*'] = 'Enabled';
        $row['E-Book*'] = 'Disabled';

        $response = $this->importRows([$row]);

        $response->assertOk()->assertJsonPath('data.successfully_imported', 1);
        $product = Product::firstOrFail();
        $this->assertTrue($product->status);
        $this->assertFalse($product->is_new_arrival);
        $this->assertTrue($product->is_best_seller);
        $this->assertFalse($product->is_e_copy);
    }

    public function test_invalid_boolean_value_is_rejected_with_row_number(): void
    {
        $row = $this->validRow();
        $row['Status*'] = 'Sometimes';

        $response = $this->importRows([$row]);

        $response->assertOk()->assertJsonPath('data.failed_row_count', 1);
        $this->assertStringContainsString(
            'Row 2: Choose Enabled or Disabled.',
            implode(' ', $response->json('data.failed_rows.0.errors'))
        );
    }

    public function test_ordered_discount_dates_import_and_blank_dates_are_allowed(): void
    {
        $row = $this->validRow();
        $row['Discount Start At'] = '2026-03-10';
        $row['Discount End At'] = '2026-03-11';

        $response = $this->importRows([$row]);

        $response->assertOk()->assertJsonPath('data.successfully_imported', 1);
        $product = Product::firstOrFail();
        $this->assertSame('2026-03-10', $product->discount_start_at->format('Y-m-d'));
        $this->assertSame('2026-03-11', $product->discount_end_at->format('Y-m-d'));

        $row['Discount Start At'] = '';
        $row['Discount End At'] = '';
        $blankDateResponse = $this->importRows([$row]);
        $blankDateResponse->assertOk()->assertJsonPath('data.successfully_imported', 1);
    }

    public function test_invalid_discount_date_and_reversed_date_range_are_rejected_by_row(): void
    {
        $invalidDate = $this->validRow();
        $invalidDate['Discount Start At'] = 'not-a-date';
        $reversedRange = $this->validRow();
        $reversedRange['Discount Start At'] = '2026-03-10';
        $reversedRange['Discount End At'] = '2026-03-09';

        $response = $this->importRows([$invalidDate, $reversedRange]);

        $response->assertOk()
            ->assertJsonPath('data.failed_row_count', 2)
            ->assertJsonPath('data.failed_rows.0.row_number', 2)
            ->assertJsonPath('data.failed_rows.1.row_number', 3);
        $this->assertStringContainsString(
            'Row 2: Discount Start At must be a valid date in YYYY-MM-DD format.',
            implode(' ', $response->json('data.failed_rows.0.errors'))
        );
        $this->assertStringContainsString(
            'Row 3: Discount End At must be on or after Discount Start At.',
            implode(' ', $response->json('data.failed_rows.1.errors'))
        );
    }

    public function test_optional_book_fields_can_be_left_blank(): void
    {
        $row = $this->validRow();
        foreach (['Title (English)', 'Title (Arabic)', 'Author', 'Detail Publisher', 'Language', 'Pages', 'ISBN', 'Publication Date', 'Description (English)', 'Description (Arabic)'] as $field) {
            $row[$field] = '';
        }

        $response = $this->importRows([$row]);

        $response->assertOk()->assertJsonPath('data.successfully_imported', 1);
        $detail = ProductDetail::firstOrFail();
        $this->assertNull($detail->title_en);
        $this->assertNull($detail->title_ar);
        $this->assertNull($detail->author);
        $this->assertNull($detail->isbn);
        $this->assertNull($detail->pages);
    }

    public function test_valid_rows_import_when_another_row_fails(): void
    {
        $invalid = $this->validRow();
        $invalid['Category*'] = 'Missing Category';
        $last = $this->validRow();
        $last['Title (English)'] = 'Last Detail Title';
        $last['Name (English)*'] = 'Last Book';

        $response = $this->importRows([$this->validRow(), $invalid, $last]);

        $response->assertOk()
            ->assertJsonPath('data.total_rows', 3)
            ->assertJsonPath('data.successfully_imported', 2)
            ->assertJsonPath('data.failed_row_count', 1)
            ->assertJsonPath('data.failed_rows.0.row_number', 3);
        $this->assertDatabaseCount('products', 2);
    }

    public function test_template_download_contains_dynamic_category_dropdown(): void
    {
        $response = $this->get('/api/admin/products/import/template', $this->authHeaders());

        $response->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );

        $path = tempnam(sys_get_temp_dir(), 'book-template-');
        file_put_contents($path, $response->streamedContent());
        $workbook = IOFactory::load($path);
        @unlink($path);

        $books = $workbook->getSheetByName('Books');
        $this->assertSame('Category*', $books->getCell('A1')->getValue());
        $this->assertSame('Title (English)', $books->getCell('B1')->getValue());
        $this->assertSame('Title (Arabic)', $books->getCell('C1')->getValue());
        $this->assertSame('Books / كتب', $workbook->getSheetByName('_Lists')->getCell('A2')->getValue());
        $this->assertSame('Enabled', $workbook->getSheetByName('_Lists')->getCell('B2')->getValue());
        $this->assertSame('Disabled', $workbook->getSheetByName('_Lists')->getCell('B3')->getValue());
        $this->assertSame('=CategoryOptions', $books->getCell('A2')->getDataValidation()->getFormula1());
        $this->assertSame('=CategoryOptions', $books->getCell('A5001')->getDataValidation()->getFormula1());
        $this->assertTrue($books->getCell('A2')->getDataValidation()->getShowDropDown());
        $this->assertTrue($books->getCell('A5001')->getDataValidation()->getShowDropDown());
        foreach (['M', 'N', 'O', 'P'] as $column) {
            foreach ([2, 5001] as $row) {
                $validation = $books->getCell($column.$row)->getDataValidation();
                $this->assertSame(DataValidation::TYPE_LIST, $validation->getType());
                $this->assertSame('=BooleanOptions', $validation->getFormula1());
                $this->assertTrue($validation->getShowDropDown());
            }
        }
        foreach (['J', 'K'] as $column) {
            foreach ([2, 5001] as $row) {
                $validation = $books->getCell($column.$row)->getDataValidation();
                $this->assertSame(DataValidation::TYPE_DATE, $validation->getType());
                $this->assertTrue($validation->getAllowBlank());
                $this->assertSame('DATE(1900,1,1)', $validation->getFormula1());
                $this->assertSame('DATE(9999,12,31)', $validation->getFormula2());
                $this->assertSame('yyyy-mm-dd', $books->getStyle($column.$row)->getNumberFormat()->getFormatCode());
            }
        }
        $this->assertSame(DataValidation::TYPE_NONE, $books->getCell('B2')->getDataValidation()->getType());
        $this->assertSame(DataValidation::TYPE_NONE, $books->getCell('C2')->getDataValidation()->getType());
        $this->assertFalse(in_array('Book Title*', $books->rangeToArray('A1:Z1')[0], true));
        $this->assertSame('hidden', $workbook->getSheetByName('_Lists')->getSheetState());
        $workbook->disconnectWorksheets();
    }

    private function validRow(): array
    {
        return [
            'Category*' => $this->category->name_en.' / '.$this->category->name_ar,
            'Title (English)' => 'English Detail Title',
            'Title (Arabic)' => 'عنوان تفصيلي',
            'Name (English)*' => 'A Sample Book',
            'Name (Arabic)*' => 'كتاب تجريبي',
            'Short Description (English)*' => 'A sample description.',
            'Short Description (Arabic)*' => 'وصف تجريبي.',
            'Price*' => 25.5,
            'Discount Percentage' => '',
            'Discount Start At' => '',
            'Discount End At' => '',
            'Stock' => '',
            'Status*' => 'TRUE',
            'New Arrival*' => 'FALSE',
            'Best Seller*' => 'FALSE',
            'E-Book*' => 'FALSE',
            'E-Book URL' => '',
            'Publisher*' => 'Sample Publisher',
            'Author' => 'Sample Author',
            'Detail Publisher' => '',
            'Language' => 'English',
            'Pages' => 120,
            'ISBN' => '',
            'Publication Date' => '',
            'Description (English)' => '',
            'Description (Arabic)' => '',
        ];
    }

    private function importRows(array $rows)
    {
        $workbook = app(ProductImportService::class)->createTemplate();
        $sheet = $workbook->getSheetByName('Books');
        foreach ($rows as $index => $row) {
            $sheet->fromArray([array_values($row)], null, 'A'.($index + 2));
        }

        $path = tempnam(sys_get_temp_dir(), 'book-import-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        $upload = new UploadedFile(
            $path,
            'books.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
        $response = $this->post('/api/admin/products/import', ['file' => $upload], $this->authHeaders());
        @unlink($path);

        return $response;
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }
}

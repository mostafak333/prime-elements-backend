<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\EbookAccessCode;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class EbookAccessCodeImportTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::factory()->create([
            'password' => Hash::make('password123'),
        ]);
        $this->token = JWTAuth::fromUser($admin);

        $this->product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'is_e_copy' => true,
            'ebook_url' => 'https://example.com/book.pdf',
            'stock' => 0,
        ]);
    }

    public function test_admin_can_import_xlsx_codes_and_sync_available_stock(): void
    {
        $response = $this->postXlsx([['access_code'], ['XLSX-1'], ['XLSX-2']]);

        $response->assertOk()
            ->assertJsonPath('data.total_rows', 2)
            ->assertJsonPath('data.imported_codes', 2)
            ->assertJsonPath('data.rejected_codes', 0);

        $this->assertDatabaseCount('ebook_access_codes', 2);
        $this->assertDatabaseHas('ebook_access_codes', [
            'product_id' => $this->product->id,
            'code' => 'XLSX-1',
            'used_at' => null,
            'order_id' => null,
            'order_item_id' => null,
        ]);
        $this->assertSame(2, $this->product->fresh()->stock);
    }

    public function test_admin_can_download_template_with_exact_import_header(): void
    {
        $response = $this->get(
            '/api/admin/products/'.$this->product->id.'/access-codes/import/template',
            $this->authHeaders()
        );

        $response->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );

        $path = tempnam(sys_get_temp_dir(), 'access-code-template-');
        file_put_contents($path, $response->streamedContent());
        $workbook = IOFactory::load($path);
        @unlink($path);

        $sheet = $workbook->getSheet(0);
        $this->assertSame('Access Codes', $sheet->getTitle());
        $this->assertSame('access_code', $sheet->getCell('A1')->getValue());
        $this->assertNull($sheet->getCell('A2')->getValue());
        $this->assertSame('A', $sheet->getHighestColumn());
        $workbook->disconnectWorksheets();
    }

    public function test_non_ebook_product_cannot_download_import_template(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'is_e_copy' => false,
            'stock' => 0,
        ]);

        $response = $this->get(
            '/api/admin/products/'.$product->id.'/access-codes/import/template',
            $this->authHeaders()
        );

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_import_csv_codes(): void
    {
        $file = UploadedFile::fake()->createWithContent('codes.csv', "access_code\nCSV-1\nCSV-2\n");
        $response = $this->postImport($file);

        $response->assertOk()
            ->assertJsonPath('data.total_rows', 2)
            ->assertJsonPath('data.imported_codes', 2)
            ->assertJsonPath('data.rejected_codes', 0);
        $this->assertDatabaseHas('ebook_access_codes', ['code' => 'CSV-1', 'product_id' => $this->product->id]);
        $this->assertSame(2, $this->product->fresh()->stock);
    }

    public function test_csv_with_another_header_is_rejected_before_import(): void
    {
        $file = UploadedFile::fake()->createWithContent('codes.csv', "code\nSHOULD-NOT-IMPORT\n");
        $response = $this->postImport($file);

        $response->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->assertDatabaseCount('ebook_access_codes', 0);
    }

    public function test_xlsx_with_another_or_additional_header_is_rejected_before_import(): void
    {
        $wrongHeader = $this->postXlsx([['code'], ['SHOULD-NOT-IMPORT']]);
        $wrongHeader->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->assertDatabaseCount('ebook_access_codes', 0);

        $extraHeader = $this->postXlsx([
            ['access_code', 'other'],
            ['SHOULD-NOT-IMPORT', 'extra'],
        ]);
        $extraHeader->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->assertDatabaseCount('ebook_access_codes', 0);
    }

    public function test_duplicate_codes_in_file_are_skipped_and_reported_by_row(): void
    {
        $file = UploadedFile::fake()->createWithContent('codes.csv', "access_code\nDUP-1\nDUP-1\n");
        $response = $this->postImport($file);

        $response->assertOk()
            ->assertJsonPath('data.total_rows', 2)
            ->assertJsonPath('data.imported_codes', 1)
            ->assertJsonPath('data.rejected_codes', 1)
            ->assertJsonPath('data.errors.0.row_number', 3);
        $this->assertDatabaseCount('ebook_access_codes', 1);
        $this->assertSame(1, $this->product->fresh()->stock);
    }

    public function test_existing_codes_are_rejected_without_overwriting_them(): void
    {
        $existing = EbookAccessCode::create([
            'product_id' => $this->product->id,
            'code' => 'EXISTING',
        ]);
        $file = UploadedFile::fake()->createWithContent('codes.csv', "access_code\nEXISTING\nNEW-CODE\n");
        $response = $this->postImport($file);

        $response->assertOk()
            ->assertJsonPath('data.imported_codes', 1)
            ->assertJsonPath('data.rejected_codes', 1)
            ->assertJsonPath('data.errors.0.row_number', 2);
        $this->assertDatabaseCount('ebook_access_codes', 2);
        $this->assertSame($existing->id, EbookAccessCode::where('code', 'EXISTING')->value('id'));
        $this->assertSame(2, $this->product->fresh()->stock);
    }

    public function test_empty_and_invalid_codes_are_rejected_while_valid_rows_import(): void
    {
        $file = $this->xlsxUpload([
            ['access_code'],
            [null],
            [str_repeat('X', 256)],
            ['VALID-CODE'],
        ]);
        $response = $this->postImport($file);

        $response->assertOk()
            ->assertJsonPath('data.total_rows', 3)
            ->assertJsonPath('data.imported_codes', 1)
            ->assertJsonPath('data.rejected_codes', 2)
            ->assertJsonPath('data.errors.0.row_number', 2)
            ->assertJsonPath('data.errors.1.row_number', 3);
        $this->assertDatabaseCount('ebook_access_codes', 1);
        $this->assertSame(1, $this->product->fresh()->stock);
    }

    public function test_import_rejects_non_ebook_products(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'is_e_copy' => false,
            'stock' => 0,
        ]);
        $file = UploadedFile::fake()->createWithContent('codes.csv', "access_code\nNOT-AN-EBOOK\n");
        $response = $this->postImport($file, $product);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
        $this->assertDatabaseCount('ebook_access_codes', 0);
    }

    public function test_import_endpoint_requires_admin_authentication(): void
    {
        $file = UploadedFile::fake()->createWithContent('codes.csv', "access_code\nUNAUTHORIZED\n");
        $response = $this->post('/api/admin/products/'.$this->product->id.'/access-codes/import', [
            'file' => $file,
        ], ['Accept' => 'application/json']);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('ebook_access_codes', 0);
    }

    public function test_repeated_import_cannot_create_a_concurrent_duplicate(): void
    {
        $this->postCsv("access_code\nRACE-CODE\n")
            ->assertOk()
            ->assertJsonPath('data.imported_codes', 1);

        $this->postCsv("access_code\nRACE-CODE\n")
            ->assertOk()
            ->assertJsonPath('data.imported_codes', 0)
            ->assertJsonPath('data.rejected_codes', 1);

        $this->assertDatabaseCount('ebook_access_codes', 1);
        $this->assertSame(1, $this->product->fresh()->stock);
    }

    private function postCsv(string $contents, ?Product $product = null)
    {
        return $this->postImport(
            UploadedFile::fake()->createWithContent('codes.csv', $contents),
            $product
        );
    }

    private function postXlsx(array $rows)
    {
        return $this->postImport($this->xlsxUpload($rows));
    }

    private function postImport(UploadedFile $file, ?Product $product = null)
    {
        $product ??= $this->product;

        return $this->post(
            '/api/admin/products/'.$product->id.'/access-codes/import',
            ['file' => $file],
            [
                'Authorization' => 'Bearer '.$this->token,
                'Accept' => 'application/json',
            ]
        );
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    private function xlsxUpload(array $rows): UploadedFile
    {
        $workbook = new Spreadsheet;
        $workbook->getActiveSheet()->fromArray($rows, null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'access-codes-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        return new UploadedFile(
            $path,
            'codes.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}

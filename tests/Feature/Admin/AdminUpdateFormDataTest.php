<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\HandleFormDataOnNonPostRequests;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUpdateFormDataTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;

    private string $token;

    private const BOUNDARY = 'X-TEST-BOUNDARY';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Role::create(['name' => 'Manager', 'guard_name' => 'api-admin']);
        Role::create(['name' => 'SuperAdmin', 'guard_name' => 'api-admin']);

        $this->superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);

        $this->token = auth()->guard('api-admin')->login($this->superAdmin);
    }

    private function createManagerAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Store Manager',
            'email' => 'manager@test.com',
            'password' => Hash::make('password'),
            'is_super' => false,
            'is_active' => true,
        ]);

        $admin->assignRole('Manager');

        return $admin;
    }

    private function multipartBody(array $fields, array $files = [], string $boundary = self::BOUNDARY): string
    {
        $body = '';

        foreach ($fields as $name => $value) {
            $body .= "--$boundary\r\n";
            $body .= "Content-Disposition: form-data; name=\"$name\"\r\n\r\n";
            $body .= $value."\r\n";
        }

        foreach ($files as $name => $file) {
            $body .= "--$boundary\r\n";
            $body .= "Content-Disposition: form-data; name=\"$name\"; filename=\"{$file['filename']}\"\r\n";
            $body .= "Content-Type: {$file['mime']}\r\n\r\n";
            $body .= $file['content']."\r\n";
        }

        return $body."--$boundary--\r\n";
    }

    private function putRaw(string $uri, string $contentType, string $body, ?string $token = null): TestResponse
    {
        $server = [
            'CONTENT_TYPE' => $contentType,
            'HTTP_ACCEPT' => 'application/json',
        ];

        if ($token) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
        }

        return $this->call('PUT', $uri, [], [], [], $server, $body);
    }

    // =========================================================================
    // PUT + multipart/form-data (the reported bug)
    // =========================================================================

    public function test_put_multipart_form_data_updates_admin_and_roles(): void
    {
        $target = $this->createManagerAdmin();

        $body = $this->multipartBody([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'phone' => '01126262626',
            'is_super' => 'true',
            'is_active' => 'true',
            'roles' => 'SuperAdmin',
        ], []);

        $response = $this->putRaw(
            "/api/admin/admins/{$target->id}",
            'multipart/form-data; boundary='.self::BOUNDARY,
            $body,
            $this->token
        );

        $response->assertOk();

        $target->refresh();

        $this->assertSame('test', $target->name);
        $this->assertSame('test@gmail.com', $target->email);
        $this->assertSame('01126262626', $target->phone);
        $this->assertTrue($target->is_super);
        $this->assertTrue($target->hasRole('SuperAdmin'));
        $this->assertFalse($target->hasRole('Manager'));
    }

    // =========================================================================
    // PUT + application/x-www-form-urlencoded
    // =========================================================================

    public function test_put_urlencoded_form_data_updates_admin_and_roles(): void
    {
        $target = $this->createManagerAdmin();

        $body = http_build_query([
            'is_super' => 'true',
            'roles' => 'SuperAdmin',
        ]);

        $response = $this->putRaw(
            "/api/admin/admins/{$target->id}",
            'application/x-www-form-urlencoded',
            $body,
            $this->token
        );

        $response->assertOk();

        $this->assertTrue($target->fresh()->hasRole('SuperAdmin'));
        $this->assertFalse($target->fresh()->hasRole('Manager'));
    }

    // =========================================================================
    // PARSER: multipart file parts
    // =========================================================================

    public function test_parser_recovers_fields_and_files_from_raw_multipart_body(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

        $body = $this->multipartBody(['name' => 'test'], [
            'avatar' => ['filename' => 'avatar.png', 'mime' => 'image/png', 'content' => $png],
        ]);

        $server = [
            'REQUEST_METHOD' => 'PUT',
            'CONTENT_TYPE' => 'multipart/form-data; boundary='.self::BOUNDARY,
        ];

        $request = Request::create('/api/admin/admins/1', 'PUT', [], [], [], $server, $body);

        $middleware = new HandleFormDataOnNonPostRequests;
        $middleware->handle($request, fn (Request $request) => new Response('ok'));

        $this->assertSame('test', $request->input('name'));

        $file = $request->file('avatar');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertSame('avatar.png', $file->getClientOriginalName());
    }

    public function test_parser_ignores_json_put_requests(): void
    {
        $request = Request::create('/api/admin/admins/1', 'PUT', [], [], [], [
            'REQUEST_METHOD' => 'PUT',
            'CONTENT_TYPE' => 'application/json',
        ], '{"name":"test"}');

        $middleware = new HandleFormDataOnNonPostRequests;
        $middleware->handle($request, fn (Request $request) => new Response('ok'));

        $this->assertSame([], $request->request->all());
    }
}

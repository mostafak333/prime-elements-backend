<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BilingualFaqTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);

        $this->token = auth()->guard('api-admin')->login($this->admin);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    public function test_admin_can_create_bilingual_faq(): void
    {
        $response = $this->postJson('/api/admin/faqs', [
            'question_en' => 'How do returns work?',
            'question_ar' => 'كيف تعمل عملية الاسترجاع؟',
            'answer_en' => 'You can request a return within 7 days.',
            'answer_ar' => 'يمكنك طلب الاسترجاع خلال 7 أيام.',
            'is_active' => true,
            'sort_order' => 1,
        ], $this->authHeaders());

        $response->assertCreated();

        $data = $response->json('data');

        $this->assertSame('How do returns work?', $data['question_en']);
        $this->assertSame('كيف تعمل عملية الاسترجاع؟', $data['question_ar']);
        $this->assertSame('You can request a return within 7 days.', $data['answer_en']);
        $this->assertSame('يمكنك طلب الاسترجاع خلال 7 أيام.', $data['answer_ar']);
        $this->assertTrue($data['is_active']);
    }

    public function test_admin_can_update_bilingual_faq(): void
    {
        $faq = Faq::create([
            'question_en' => 'Old?',
            'question_ar' => 'قديم؟',
            'answer_en' => 'Old answer.',
            'answer_ar' => 'إجابة قديمة.',
        ]);

        $response = $this->putJson('/api/admin/faqs/'.$faq->id, [
            'answer_en' => 'New answer.',
        ], $this->authHeaders());

        $response->assertOk();

        $data = $response->json('data');
        $this->assertSame('New answer.', $data['answer_en']);
        $this->assertSame('إجابة قديمة.', $data['answer_ar']);
    }

    public function test_admin_create_requires_both_languages(): void
    {
        $response = $this->postJson('/api/admin/faqs', [
            'question_en' => 'Missing Arabic',
            'answer_en' => 'Missing Arabic answer',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['question_ar', 'answer_ar']);
    }

    public function test_user_faqs_endpoint_returns_both_languages(): void
    {
        Faq::create([
            'question_en' => 'How do returns work?',
            'question_ar' => 'كيف تعمل عملية الاسترجاع؟',
            'answer_en' => 'Within 7 days.',
            'answer_ar' => 'خلال 7 أيام.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Faq::create([
            'question_en' => 'Inactive item',
            'question_ar' => 'عنصر غير نشط',
            'answer_en' => 'Should not appear.',
            'answer_ar' => 'لا يجب ظهوره.',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/faqs');

        $response->assertOk();

        $faq = collect($response->json('data.faqs'))->first();

        $this->assertSame('How do returns work?', $faq['question_en']);
        $this->assertSame('كيف تعمل عملية الاسترجاع؟', $faq['question_ar']);
        $this->assertSame('Within 7 days.', $faq['answer_en']);
        $this->assertSame('خلال 7 أيام.', $faq['answer_ar']);
        $this->assertCount(1, $response->json('data.faqs'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    private const ENGLISH_SUBSCRIBED = 'Subscribed successfully.';

    private const ARABIC_SUBSCRIBED = 'تم الاشتراك بنجاح.';

    // =========================================================================
    // LOCALE NEGOTIATION
    // =========================================================================

    public function test_it_defaults_to_english_when_no_accept_language_header_is_sent(): void
    {
        $response = $this->postJson('/api/subscribe', [
            'email' => 'default@example.com',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => self::ENGLISH_SUBSCRIBED,
            ]);
    }

    public function test_it_uses_english_when_accept_language_is_english(): void
    {
        $response = $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/subscribe', [
                'email' => 'english@example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => self::ENGLISH_SUBSCRIBED,
            ]);
    }

    public function test_it_uses_arabic_when_accept_language_is_arabic(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/subscribe', [
                'email' => 'arabic@example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => self::ARABIC_SUBSCRIBED,
            ]);
    }

    public function test_it_uses_arabic_when_accept_language_carries_a_region_subtag(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar-EG')
            ->postJson('/api/subscribe', [
                'email' => 'arabic-eg@example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => self::ARABIC_SUBSCRIBED,
            ]);
    }

    public function test_it_falls_back_to_english_for_an_unsupported_language(): void
    {
        $response = $this->withHeader('Accept-Language', 'fr-FR')
            ->postJson('/api/subscribe', [
                'email' => 'french@example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => self::ENGLISH_SUBSCRIBED,
            ]);
    }

    public function test_it_picks_the_language_with_the_highest_quality_factor(): void
    {
        $response = $this->withHeader('Accept-Language', 'en;q=0.5,ar;q=0.9')
            ->postJson('/api/subscribe', [
                'email' => 'quality-arabic@example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => self::ARABIC_SUBSCRIBED,
            ]);
    }

    public function test_it_keeps_english_when_english_has_the_highest_quality_factor(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar;q=0.3,en;q=0.9')
            ->postJson('/api/subscribe', [
                'email' => 'quality-english@example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => self::ENGLISH_SUBSCRIBED,
            ]);
    }

    public function test_it_skips_unsupported_languages_when_negotiating(): void
    {
        $response = $this->withHeader('Accept-Language', 'de,fr;q=0.8,ar;q=0.6')
            ->postJson('/api/subscribe', [
                'email' => 'fallback@example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => self::ARABIC_SUBSCRIBED,
            ]);
    }

    // =========================================================================
    // LOCALIZED VALIDATION MESSAGES
    // =========================================================================

    public function test_validation_messages_are_localized(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/subscribe', [
                'email' => 'not-an-email',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertSame(
            'يجب أن يكون حقل البريد الإلكتروني بريدًا إلكترونيًا صحيحًا.',
            $response->json('errors.email.0')
        );
    }

    public function test_validation_messages_default_to_english(): void
    {
        $response = $this->postJson('/api/subscribe', [
            'email' => 'not-an-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertSame(
            'The email field must be a valid email address.',
            $response->json('errors.email.0')
        );
    }

    // =========================================================================
    // LOCALIZED AUTH MESSAGES
    // =========================================================================

    public function test_authentication_errors_are_localized(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/login', [
                'email' => 'user@test.com',
                'password' => 'wrong-password',
            ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'بيانات الاعتماد المقدمة لا تتطابق مع سجلاتنا.',
            ]);
    }
}

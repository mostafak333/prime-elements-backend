<?php

namespace Tests\Feature\User;

use App\Jobs\SendEmailJob;
use App\Mail\ContactUsMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContactUsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_us_dispatchs_email_to_config_recipient(): void
    {
        Queue::fake();

        config(['app.contactusemail' => 'contact@example.com']);

        $response = $this->postJson('/api/contact-us', [
            'full_name' => 'Ahmed Ali',
            'email' => 'ahmed@test.com',
            'subject' => 'Question about my order',
            'message' => 'Hello, I need some help.',
        ]);

        $response->assertOk();

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job) {
            if ($job->email !== 'contact@example.com') {
                return false;
            }

            $mail = $job->mailable;

            return $mail instanceof ContactUsMail
                && $mail->fullName === 'Ahmed Ali'
                && $mail->email === 'ahmed@test.com'
                && $mail->subjectText === 'Question about my order'
                && $mail->message === 'Hello, I need some help.'
                && str_contains($mail->buildBody(), 'Ahmed Ali')
                && str_contains($mail->buildBody(), 'Hello, I need some help.');
        });
    }

    public function test_contact_us_requires_all_fields(): void
    {
        $response = $this->postJson('/api/contact-us', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name', 'email', 'subject', 'message']);
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public string $email,
        public Mailable $mailable,
    ) {}

    public function handle(): void
    {
        try {
            Mail::to($this->email)->send($this->mailable);
        } catch (\Throwable $e) {
            Log::error('Email sending failed', [
                'email' => $this->email,
                'mailable' => get_class($this->mailable),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}

<?php

namespace App\Http\Controllers\UserApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ContactUsRequest;
use App\Jobs\SendEmailJob;
use App\Mail\ContactUsMail;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ContactUsController extends Controller
{
    use ApiResponse;

    public function store(ContactUsRequest $request): JsonResponse
    {
        $data = $request->validated();

        SendEmailJob::dispatch(
            config('app.contactusemail'),
            new ContactUsMail(
                $data['full_name'],
                $data['email'],
                $data['subject'],
                $data['message'],
            )
        );

        return $this->success(null, 'Your message has been sent successfully.');
    }
}

<?php

namespace App\Http\Requests\User\Wishlist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = auth()->guard('api-user')->id();

        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                Rule::unique('wishlists', 'product_id')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.unique' => 'This product is already in your wishlist.',
        ];
    }
}

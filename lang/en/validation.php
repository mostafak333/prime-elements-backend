<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the application to override the
    | default validation messages. Only the customised lines are declared here
    | so the framework defaults keep applying to every other rule.
    |
    */

    'custom' => [
        'per_page' => [
            'integer' => 'Per page must be a number.',
            'min' => 'Per page must be at least 1.',
            'max' => 'Per page cannot exceed 100.',
        ],

        'status' => [
            'in' => 'Status must be one of: active, inactive, pending, suspended.',
        ],

        'parent_id' => [
            'different' => 'A category cannot be its own parent.',
            'exists' => 'The selected parent category does not exist.',
        ],

        'slug' => [
            'unique' => 'This slug is already taken.',
        ],

        'title_id' => [
            'required' => 'The title field is required.',
            'exists' => 'The selected title does not exist.',
        ],

        'terms_and_condition_agreed' => [
            'accepted' => 'You must agree to the terms and conditions.',
            'required' => 'You must agree to the terms and conditions.',
        ],

        'privacy_policy_agreed' => [
            'accepted' => 'You must agree to the privacy policy.',
            'required' => 'You must agree to the privacy policy.',
        ],

        'address' => [
            'full_name' => [
                'required' => 'Full name is required.',
            ],
            'phone' => [
                'required' => 'Phone number is required.',
            ],
        ],

        'categories' => [
            'exists' => 'One or more selected categories are invalid.',
        ],

        'formats' => [
            'in' => 'Invalid format selected. Must be printed, ebook, or both.',
        ],

        'min_price' => [
            'min' => 'Minimum price must be at least 0.',
        ],

        'max_price' => [
            'min' => 'Maximum price must be at least 0.',
            'gte' => 'Maximum price must be greater than or equal to minimum price.',
        ],

        'availability' => [
            'in' => 'Invalid availability option.',
        ],

        'product_id' => [
            'unique' => 'This product is already in your wishlist.',
        ],

        'ebook_url' => [
            'required' => 'The ebook URL is required for e-books.',
            'url' => 'The ebook URL must be a valid URL.',
        ],

        'codes' => [
            'required' => 'At least one access code is required.',
            'array' => 'Access codes must be provided as an array.',
        ],

        'codes.*' => [
            'required' => 'Each access code is required.',
        ],
    ],
];

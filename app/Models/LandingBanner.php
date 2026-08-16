<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandingBanner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title_en',
        'title_ar',
        'description_en',
        'description_ar',
        'image',
        'button_enabled',
        'button_name_en',
        'button_name_ar',
        'button_link',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'button_enabled' => 'boolean',
            'status' => 'boolean',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[\Attribute]
class Fillable
{
    public function __construct(public array $fields) {}
}

class Category extends Model
{
    #[Fillable(['name', 'slug', 'description', 'status'])]
    protected $fillable = ['name', 'slug', 'description', 'status'];

    /**
     * Auto-generate slug on create/update
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = $category->slug ?? Str::slug($category->name);
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Scope: active categories only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

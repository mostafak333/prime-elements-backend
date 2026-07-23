<?php

namespace App\Models;

use App\Models\Admin;
use App\Models\Image;
use App\Models\Title;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'title_id',
        'image_id',
        'name_en',
        'name_ar',
        'slug',
        'description_en',
        'description_ar',
        'status',
        'is_filter',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_filter' => 'boolean',
    ];

    /**
     * Get the parent category (self-referential).
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get all child categories (self-referential).
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->where('status', true);
    }

    /**
     * Get the title associated with this category.
     */
    public function title()
    {
        return $this->belongsTo(Title::class, 'title_id');
    }

    /**
     * Get the image associated with this category.
     */
    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id');
    }

    /**
     * Get all descendant categories (recursive).
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Scope to filter active categories only.
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    /**
     * Administrator who created the record.
     */
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Administrator who last updated the record.
     */
    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function recursiveChildren()
    {
        return $this->children()->with('recursiveChildren');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['title_id'])) {
            $query->where('title_id', $filters['title_id']);
        }

        if (isset($filters['name_en'])) {
            $query->where('name_en', 'LIKE', '%' . $filters['name_en'] . '%');
        }

        if (isset($filters['name_ar'])) {
            $query->where('name_ar', 'LIKE', '%' . $filters['name_ar'] . '%');
        }

        if (isset($filters['slug'])) {
            $query->where('slug', 'LIKE', '%' . $filters['slug'] . '%');
        }

        if (isset($filters['description_en'])) {
            $query->where('description_en', 'LIKE', '%' . $filters['description_en'] . '%');
        }

        if (isset($filters['description_ar'])) {
            $query->where('description_ar', 'LIKE', '%' . $filters['description_ar'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_filter'])) {
            $query->where('is_filter', $filters['is_filter']);
        }

        return $query;
    }
}

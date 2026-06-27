<?php

namespace App\Models;

use App\Models\Admin;
use App\Models\Image;
use App\Models\Title;
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
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
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
        return $this->hasMany(Category::class, 'parent_id');
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
    public function scopeActive($query)
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
}

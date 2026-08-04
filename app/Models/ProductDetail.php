<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'title_en',
        'title_ar',
        'description_en',
        'description_ar',
        'book_title',
        'author',
        'publisher',
        'language',
        'pages',
        'isbn',
        'publication_date',
    ];

    protected $casts = [
        'pages' => 'integer',
        'publication_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns the detail.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include active product details.
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the full book title with author.
     */
    public function getFullTitleAttribute()
    {
        return $this->book_title . ' - ' . $this->author;
    }

    /**
     * Get formatted publication date.
     */
    public function getFormattedPublicationDateAttribute()
    {
        return $this->publication_date ? $this->publication_date->format('F d, Y') : null;
    }
}

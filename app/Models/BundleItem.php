<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BundleItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bundle_id',
        'product_id',
        'quantity',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }
}

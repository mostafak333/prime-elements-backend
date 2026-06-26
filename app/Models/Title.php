<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Title extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_ar',
        'created_by',
        'updated_by',
    ];

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

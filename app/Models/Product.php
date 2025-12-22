<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    // Enable soft delete functionality
    // This allows records to be marked as deleted without removing them from the database
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * These fields can be safely filled using create() or update().
     */
    protected $fillable = [
        'name',        // Product name
        'description', // Product description (optional)
        'price',       // Product price
        'status',      // Product status (active, inactive, deleted)
        'created_by',  // User ID who created the product
        'updated_by'   // User ID who last updated the product
    ];

    /**
     * Attribute casting.
     * Automatically formats price with 2 decimal places.
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];
}

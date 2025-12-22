<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    /**
     * This method is called for each row in the Excel file.
     * Each row is converted into a Product model instance.
     */
    public function model(array $row)
    {
        return new Product([

            // Product name column from Excel
            // Example Excel heading: name
            'name' => $row['name'] ?? null,

            // Product description column from Excel (optional)
            // Example Excel heading: description
            'description' => $row['description'] ?? null,

            // Product price column from Excel
            // Default value is 0 if price is missing
            // Example Excel heading: price
            'price' => $row['price'] ?? 0,

            // Product status column from Excel
            // Allowed values: active, inactive
            // Default is 'active' if status is missing
            // Example Excel heading: status
            'status' => $row['status'] ?? 'active',

            // Static user ID for created_by
            // Can be replaced with Auth::id() when authentication is implemented
            'created_by' => 1,
        ]);
    }
}

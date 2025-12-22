<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    /**
     * Fetch all products from the database.
     * This data will be written row-by-row into the Excel file.
     */
    public function collection()
    {
        return Product::select(
            'id',          // Product ID
            'name',        // Product name
            'description', // Product description
            'price',       // Product price
            'status',      // Product status (active, inactive, deleted)
            'created_by',  // User ID who created the product
            'updated_by',  // User ID who last updated the product
            'created_at',  // Record creation timestamp
            'updated_at'   // Record last update timestamp
        )->get();
    }

    /**
     * Define column headings for the Excel file.
     * These headings appear as the first row in the exported sheet.
     */
    public function headings(): array
    {
        return [
            'ID',          // Product ID
            'Name',        // Product name
            'Description', // Product description
            'Price',       // Product price
            'Status',      // Product status
            'Created By',  // Created by user ID
            'Updated By',  // Updated by user ID
            'Created At',  // Creation date
            'Updated At',  // Last updated date
        ];
    }
}

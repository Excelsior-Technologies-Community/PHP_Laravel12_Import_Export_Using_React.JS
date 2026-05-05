<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Product([
            'name' => $row['name'] ?? null,
            'description' => $row['description'] ?? null,
            'price' => $row['price'] ?? 0,
            'status' => $row['status'] ?? 'active',
            'created_by' => 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ];
    }
}
# PHP_Laravel12_Import_Export_Using_React.JS


## Introduction

**PHP_Laravel12_Import_Export_Using_React.JS** is a **Laravel 12 + React.js web application** designed to manage product data efficiently, with a primary focus on **importing and exporting products using Excel or CSV files**.  

While the project includes basic CRUD operations (**Create, Update, Delete**) for products, the **core functionality is centered around bulk data management**, allowing businesses or inventory systems to seamlessly handle large volumes of product information.  

The project uses **Blade templates with React (Vite)** for a modern frontend experience, while Laravel handles backend operations, including Excel import/export using the **Maatwebsite Excel package**. This makes it a practical and professional solution for inventory management, reporting, or business data handling.

---

## Project Overview

* **Backend:** Laravel 12  
* **Frontend:** React.js (Vite) embedded in Blade  
* **Database:** MySQL  
* **Excel Integration:** Maatwebsite Excel package
  
---

## Key Highlights:

* Import products from Excel / CSV files  
* Export products to Excel  
* Full product CRUD (Create, Update, Delete)  
* Soft delete support  
* React.js front-end embedded inside Blade templates  
* Data passed to React using `window.productsData`  

---

## Features

1. **Import Products from Excel / CSV** (Primary Focus)  
2. **Export Products to Excel**  
3. **Product CRUD Operations** (Create / Update / Delete)  
4. **Soft Delete Support**  
5. **React.js front-end embedded in Blade**  
6. **Data initialized via `window.productsData`**  

---

## Project Structure

```
PHP_Laravel12_Import_Export_Using_React.JS/
├── app/
│   ├── Http/Controllers/
│   │   └── ProductController.php
│   ├── Imports/
│   │   └── ProductsImport.php
│   ├── Exports/
│   │   └── ProductsExport.php
│   └── Models/
│       └── Product.php
├── database/
│   └── migrations/
│       └── 2025_12_18_000000_create_products_table.php
├── resources/
│   ├── js/
│   │   ├── app.js
│   │   └── Product.jsx
│   └── views/
│       └── products.blade.php
├── routes/
│   └── web.php
├── vite.config.js
├── .env
└── README.md
```

---

## Step 1: Create Laravel 12 Project

```bash
composer create-project laravel/laravel PHP_Laravel12_Import_Export_Using_React.JS "12.*"

cd PHP_Laravel12_Import_Export_Using_React.JS
```

---

## Step 2: Install React & Vite

```bash
npm install
npm install react react-dom
npm install -D @vitejs/plugin-react
npm run dev
```

---

## Step 3: Configure Vite

**vite.config.js**

```js
import { defineConfig } from 'vite';                   // Import Vite's defineConfig function
import laravel from 'laravel-vite-plugin';            // Import Laravel plugin for Vite
import react from '@vitejs/plugin-react';             // Import React plugin for Vite
import tailwindcss from '@tailwindcss/vite';          // Import TailwindCSS plugin for Vite

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],  // Entry points for CSS and JS
            refresh: true,                                            // Enable automatic refresh on changes
        }),
        react(),       // Enable React support
        tailwindcss(), // Enable TailwindCSS support
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'], // Ignore Laravel view cache files to avoid unnecessary reloads
        },
    },
});
```

---

## Step 4: React Entry File

**resources/js/app.js**

```js
// Entry point for React
import './Product.jsx';
```

---

## Step 5: Install Excel Package

```bash
composer require maatwebsite/excel
```

---

## Step 6: Database Configuration

**.env**

```env
DB_DATABASE=import_export_db
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate
```

---

## Step 7: Migration table 

```
php artisan make:migration create_products_table
```

This will create:
database/migrations/xxxx_xx_xx_xxxxxx_create_products_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This method is executed when you run: php artisan migrate
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            // Primary key (Auto Increment ID)
            $table->id();

            // Product name
            $table->string('name');

            // Product description (optional)
            $table->text('description')->nullable();

            // Product price with 2 decimal precision
            $table->decimal('price', 10, 2);

            // Product status
            // active   → product is available
            // inactive → product is disabled
            // deleted  → product is soft deleted
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');

            // User ID who created the product
            $table->integer('created_by')->nullable();

            // User ID who last updated the product
            $table->integer('updated_by')->nullable();

            // Soft delete column (deleted_at)
            // Allows deleting data without removing it from database
            $table->softDeletes();

            // created_at and updated_at timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * This method is executed when you run: php artisan migrate:rollback
     */
    public function down(): void
    {
        // Drop products table
        Schema::dropIfExists('products');
    }
};
```

---

## Step 8: Product Model

```
php artisan make:model Product
```

This will create:
app/Models/Product.php

```php
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
```

---

## Step 9: Import Class

```
php artisan make:import ProductsImport --model=Product
```

This command creates:
app/Imports/ProductsImport.php


```php
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
```

---

## Step 10: Export Class

```
php artisan make:export ProductsExport --model=Product
```

This command creates:
app/Exports/ProductsExport.php


```php
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
```

---

## Step 11: Controller (FULL CRUD + Import + Export)

```
php artisan make:controller ProductController 
```

File: app/Http/Controllers/ProductController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Imports\ProductsImport;
use App\Exports\ProductsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display all products.
     * Product data is passed to Blade
     * and React consumes it via window.productsData.
     */
    public function index()
    {
        // Fetch all products ordered by ID in ascending order
        $products = Product::orderBy('id', 'asc')->get();

        // Return Blade view and pass products data
        return view('products', compact('products'));
    }

    /**
     * Store a new product in the database.
     * Handles CREATE operation from React form.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        // Create a new product record
        Product::create([
            'name' => $request->name,               // Product name
            'description' => $request->description, // Product description
            'price' => $request->price,             // Product price
            'status' => $request->status,            // Product status
            'created_by' => 1,                       // Static user ID (replace with auth()->id())
        ]);

        // Redirect back with success message
        return redirect()->back()->with('success', 'Product Created Successfully');
    }

    /**
     * Fetch a single product for editing.
     * Returns JSON response used by React.
     */
    public function edit($id)
    {
        // Find product by ID or throw 404 error
        $product = Product::findOrFail($id);

        // Return product data as JSON
        return response()->json($product);
    }

    /**
     * Update an existing product.
     * Handles UPDATE operation from React form.
     */
    public function update(Request $request, $id)
    {
        // Find the product by ID
        $product = Product::findOrFail($id);

        // Validate incoming request data
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        // Update product record
        $product->update([
            'name' => $request->name,               // Updated product name
            'description' => $request->description, // Updated description
            'price' => $request->price,             // Updated price
            'status' => $request->status,            // Updated status
            'updated_by' => 1,                       // Static user ID (replace with auth()->id())
        ]);

        // Redirect back with success message
        return redirect()->back()->with('success', 'Product Updated Successfully');
    }

    /**
     * Soft delete a product.
     * Marks status as 'deleted' and performs soft delete.
     */
    public function destroy($id)
    {
        // Find product by ID
        $product = Product::findOrFail($id);

        // Update product status to 'deleted'
        $product->update([
            'status' => 'deleted',
            'updated_by' => 1, // Static user ID
        ]);

        // Perform soft delete (sets deleted_at timestamp)
        $product->delete();

        // Return JSON response for React
        return response()->json(['success' => true]);
    }

    /**
     * Import products from Excel or CSV file.
     */
    public function import(Request $request)
    {
        // Validate uploaded file type
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        // Import data using ProductsImport class
        Excel::import(new ProductsImport, $request->file('file'));

        // Redirect back with success message
        return redirect()->back()->with('success', 'Products Imported Successfully');
    }

    /**
     * Export products to Excel file.
     */
    public function export()
    {
        // Download Excel file using ProductsExport class
        return Excel::download(new ProductsExport, 'products.xlsx');
    }
}
```

---

## Step 12: Routes

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

// Products CRUD
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::post('/products/{id}/update', [ProductController::class, 'update'])->name('products.update');
Route::post('/products/{id}/delete', [ProductController::class, 'destroy'])->name('products.destroy');

// Import products (Excel/CSV)
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

// Export products (Excel)
Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
```

---

## Step 13: React Component (Product.jsx)

FIle: resources/js/Product.jsx

Explaination: Product.jsx is a single React component file that manages the complete Product module UI in Laravel.

It handles listing, creating, editing, and deleting products using React state while submitting forms to Laravel routes.

All product data is passed from Blade using window.productsData, keeping the project simple and beginner-friendly.

```jsx
import React, { useState } from "react";
import { createRoot } from "react-dom/client";

/**
 * ProductForm Component
 * Handles both CREATE and EDIT operations
 * Used for adding a new product or editing an existing one
 */
function ProductForm({ product, onBack }) {
  // Form state initialization (prefilled in edit mode)
  const [name, setName] = useState(product?.name ?? "");
  const [description, setDescription] = useState(product?.description ?? "");
  const [price, setPrice] = useState(product?.price ?? "");
  const [status, setStatus] = useState(product?.status ?? "active");

  return (
    <div>
      {/* Back button to return to product list */}
      <button className="btn btn-secondary mb-3" onClick={onBack}>
        ← Back to List
      </button>

      {/* Product Create / Update Form */}
      <form
        method="POST"
        action={product ? `/products/${product.id}/update` : "/products/store"}
      >
        {/* CSRF token for Laravel security */}
        <input
          type="hidden"
          name="_token"
          value={document.querySelector('meta[name="csrf-token"]').content}
        />

        {/* Form heading based on mode */}
        <h3>{product ? "Edit Product" : "Add Product"}</h3>

        {/* Product Name Field */}
        <label className="form-label">Name</label>
        <input
          type="text"
          name="name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="form-control mb-2"
          required
        />

        {/* Product Description Field */}
        <label className="form-label">Description</label>
        <textarea
          name="description"
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          className="form-control mb-2"
        ></textarea>

        {/* Product Price Field */}
        <label className="form-label">Price</label>
        <input
          type="number"
          step="0.01"
          name="price"
          value={price}
          onChange={(e) => setPrice(e.target.value)}
          className="form-control mb-2"
          required
        />

        {/* Product Status Dropdown */}
        <label className="form-label">Status</label>
        <select
          name="status"
          value={status}
          onChange={(e) => setStatus(e.target.value)}
          className="form-control mb-3"
        >
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        {/* Submit Button */}
        <button className="btn btn-primary">
          {product ? "Update" : "Save"}
        </button>
      </form>
    </div>
  );
}

/**
 * ProductIndex Component
 * Displays product list and manages Add, Edit, and Delete actions
 */
function ProductIndex({ products }) {
  // Product list state (excluding deleted products)
  const [list, setList] = useState(
    products.filter((p) => p.status !== "deleted")
  );

  // State to track editing and adding modes
  const [editingProduct, setEditingProduct] = useState(null);
  const [addingProduct, setAddingProduct] = useState(false);

  /**
   * Delete product handler
   * Performs soft delete by setting status to 'deleted'
   */
  const handleDelete = (id) => {
    if (!confirm("Are you sure to delete?")) return;

    // Optimistic UI update (remove row immediately)
    setList(list.filter((p) => p.id !== id));

    // Send delete request to Laravel
    fetch(`/products/${id}/delete`, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
    }).then(() => console.log("Deleted"));
  };

  // Show Create Product form
  if (addingProduct) {
    return (
      <ProductForm
        product={null}
        onBack={() => setAddingProduct(false)}
      />
    );
  }

  // Show Edit Product form
  if (editingProduct) {
    return (
      <ProductForm
        product={editingProduct}
        onBack={() => setEditingProduct(null)}
      />
    );
  }

  // Show Products List Table
  return (
    <div>
      <h2>Products List</h2>

      {/* Add Product Button */}
      <button
        className="btn btn-primary mb-3"
        onClick={() => setAddingProduct(true)}
      >
        + Add Product
      </button>

      {/* Products Table */}
      <table className="table table-bordered">
        <thead>
          <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          {list.map((product) => (
            <tr key={product.id}>
              <td>{product.id}</td>
              <td>{product.name}</td>
              <td>{product.description}</td>
              <td>{product.price}</td>
              <td>{product.status}</td>
              <td>
                {/* Edit Button */}
                <button
                  className="btn btn-sm btn-warning me-2"
                  onClick={() => setEditingProduct(product)}
                >
                  Edit
                </button>

                {/* Delete Button */}
                <button
                  className="btn btn-sm btn-danger"
                  onClick={() => handleDelete(product.id)}
                >
                  Delete
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

/**
 * Render React application
 * Mounts ProductIndex component inside Blade view
 */
createRoot(document.getElementById("app")).render(
  <ProductIndex products={window.productsData} />
);
```

---

## Step 14: Blade View (products.blade.php)

File: resources/views/products.blade.php

Explaination: products.blade.php acts as the main layout file that loads the React Product module using Vite and provides a mount point for the React application.

It also includes Import and Export Excel controls and securely passes product data from Laravel to React using window.productsData.


```blade
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Laravel React Import/Export</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Vite React -->
        @viteReactRefresh
        @vite('resources/js/Product.jsx')

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body>
    <div class="container mt-5">

       <!-- Import/Export Section -->
<div class="d-flex flex-column flex-md-row justify-content-end align-items-center mb-4 gap-2">

    <!-- Export Button -->
    <a href="{{ route('products.export') }}" class="btn btn-success d-flex align-items-center justify-content-center gap-1" style="height: 42px;">
        <i class="bi bi-download"></i> Export Excel
    </a>

    <!-- Import Form -->
    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
        @csrf
        <input type="file" name="file" accept=".xlsx,.csv" class="form-control" style="height: 42px;">
        <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center gap-1" style="height: 42px;">
            <i class="bi bi-upload"></i> Import Excel
        </button>
    </form>
</div>


        <!-- React App Mount Point -->
        <div id="app"></div>
    </div>

    <script>
        // Pass products data to React
        window.productsData = @json($products ?? []);
    </script>

    <!-- Optional Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
```

---

## Run Project

```bash
php artisan serve
npm run dev
```

Open:

```
http://127.0.0.1:8000/products
```

---

## Output

**Create Product**

<img width="1919" height="1032" alt="Screenshot 2025-12-22 104546" src="https://github.com/user-attachments/assets/c5fc65cc-29ad-4364-91c2-4fd007b2766e" />


**Import Product**

<img width="1919" height="1027" alt="Screenshot 2025-12-19 122613" src="https://github.com/user-attachments/assets/795e5e64-5523-4431-a6d3-0aae6b0a428e" />

<img width="1919" height="1033" alt="Screenshot 2025-12-22 105323" src="https://github.com/user-attachments/assets/7647d016-5279-4ae3-ba7d-bf61d76bfa28" />


**Export Product**

<img width="1896" height="890" alt="Screenshot 2025-12-19 122658" src="https://github.com/user-attachments/assets/8262f778-1f80-4fab-b983-587986646458" />


**Edit Product**

<img width="1919" height="1032" alt="Screenshot 2025-12-22 105310" src="https://github.com/user-attachments/assets/6e14b974-73f6-4e19-a2de-ec62f2fb9b89" />

<img width="1919" height="1033" alt="Screenshot 2025-12-22 105323" src="https://github.com/user-attachments/assets/a5b97f89-88e7-490a-b4f8-76ca2ab0f914" />


**Delete Product**

<img width="1919" height="1026" alt="Screenshot 2025-12-22 105456" src="https://github.com/user-attachments/assets/97d93f75-8d83-4b25-8813-46037557222b" />

<img width="1919" height="1033" alt="Screenshot 2025-12-22 105505" src="https://github.com/user-attachments/assets/0ba2472f-7de6-4067-a600-a614ad2cf200" />


---


**Your PHP_Laravel12_Import_Export_Using_React.JS project is COMPLETED**


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

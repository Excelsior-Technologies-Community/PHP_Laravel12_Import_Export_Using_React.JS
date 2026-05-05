<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Imports\ProductsImport;
use App\Exports\ProductsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\FromArray;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::withTrashed()->orderBy('id', 'asc')->get();
        return view('products', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'status' => $request->status,
            'created_by' => 1,
        ]);

        return redirect()->back()->with('success', 'Product Created Successfully');
    }

    public function edit($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'status' => $request->status,
            'updated_by' => 1,
        ]);

        return redirect()->back()->with('success', 'Product Updated Successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => 'deleted', 'updated_by' => 1]);
        $product->delete();
        return response()->json(['success' => true]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048',
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Products Imported Successfully');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return redirect()->back()->withErrors($e->failures());
        }
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function exportSelected(Request $request)
    {
        $ids = $request->input('ids', []);
        return Excel::download(new class($ids) implements \Maatwebsite\Excel\Concerns\FromQuery, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $ids;
            public function __construct($ids) { $this->ids = $ids; }
            public function query() { return Product::withTrashed()->whereIn('id', $this->ids); }
            public function headings(): array { 
                return ['ID', 'Name', 'Description', 'Price', 'Status', 'Created By', 'Updated By', 'Deleted At', 'Created At', 'Updated At']; 
            }
        }, 'selected_products.xlsx');
    }

    public function downloadTemplate()
    {
        $headings = [['name', 'description', 'price', 'status']];
        return Excel::download(new class($headings) implements FromArray {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'product_template.xlsx');
    }

    public function exportPDF(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return redirect()->back()->with('error', 'Select products first');
        $products = Product::withTrashed()->whereIn('id', $ids)->get();
        $pdf = Pdf::loadView('pdf.products', compact('products'));
        return $pdf->download('products_report.pdf');
    }
}
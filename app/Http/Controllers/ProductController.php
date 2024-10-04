<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1); // Get the current page
        $offset = ($currentPage - 1) * $perPage; // Calculate the offset
        $search = $request->input('search', ''); // Get the search term

        $productsQuery = Product::query();
        if (!empty($search)) {
            $productsQuery->where(function ($query) use ($search) {
                $query->where('product_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('category', 'LIKE', '%' . $search . '%'); // Adjust fields as necessary
            });
        }

        // Get the total number of products for pagination
        $total = $productsQuery->count();

        // Apply limit and offset
        $products = $productsQuery->offset($offset)->limit($perPage)->get();

        // Prepare the response with pagination info
        return response()->json([
            'data' => $products,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => (int) ceil($total / $perPage),
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'product_name' => 'required|string|max:255|unique:products,product_name', // Ensure product_name is unique
            'category' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric', // Optional field, adjust as needed
        ]);

        // Create the product
        $product = Product::create($request->all());

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Product::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'product_name' => 'required|string|unique:products,product_name,' . $id,
        ]);

        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Product::destroy($id);
        return response()->json(null, 204);
    }
}

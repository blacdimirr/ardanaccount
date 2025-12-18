<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use Illuminate\Http\Request;

class ApiExternalController extends Controller
{

    public function get_products()
    {
       $productos = ProductService::with(['category', 'unit', 'taxes'])->get();

        return response()->json([
            'success' => true,
            'data' => $productos,
        ]);
    }

    public function get_product_bySku($sku)
    {
        $producto = ProductService::with(['category', 'unit', 'taxes'])->where('sku', $sku)->get();

        return response()->json([
            'success' => true,
            'data' => $producto,
        ]);
    }

     public function get_all_category()
    {
        $category = ProductServiceCategory::all();

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }
    public function get_all_units()
    {
        $units = ProductServiceUnit::all();

        return response()->json([
            'success' => true,
            'data' => $units,
        ]);
    }

    public function update_product_bySku($sku, Request $request)
    {
        
        $producto = ProductService::where('sku', $sku)->first();

        if (!$producto) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        if (!$request->has('quantity')) {
            return response()->json(['success' => false, 'message' => 'Quantity field is required'], 422);
        }


        $producto->quantity_received = $producto->quantity_received + $request->input('quantity');
        $producto->save();

        return response()->json([
            'success' => true,
            'data' => $producto,
        ]);
    }
}

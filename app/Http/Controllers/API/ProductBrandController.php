<?php

namespace App\Http\Controllers\API;

use App\Models\ProductBrand;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProductBrandController extends Controller
{
    // Display a listing of product brands
    public function index()
    {
        $productBrands = ProductBrand::with(['createdBy', 'updatedBy'])->get();
        return response()->json($productBrands);
    }

    // Display the specified product brand
    public function show($id)
    {
        $productBrand = ProductBrand::with(['createdBy', 'updatedBy'])->find($id);

        if (!$productBrand) {
            return response()->json(['message' => 'Product brand not found'], 404);
        }

        return response()->json($productBrand);
    }

    // Store a newly created product brand
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'country_of_origin' => 'required|string|max:255',
        ]);

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $logoUrl = $this->uploadPhoto($request->file('logo'), 'product_brand_logos');
        }

        $productBrand = ProductBrand::create([
            'name' => $request->name,
            'description' => $request->description,
            'logo_url' => $logoUrl,
            'country_of_origin' => $validatedData['country_of_origin'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Product brand created successfully', 'data' => $productBrand]);
    }

    private function uploadPhoto($photo, $folderPath)
    {
        $publicPath = public_path($folderPath);
        if (!File::exists($publicPath)) {
            File::makeDirectory($publicPath, 0777, true, true);
        }

        $fileName = time() . '_' . $photo->getClientOriginalName();
        $photo->move($publicPath, $fileName);

        return '/' . $folderPath . '/' . $fileName;
    }

    // Update the specified product brand
    public function update(Request $request, $productBrandId)
    {
        $validatedData =  $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'country_of_origin' => 'required|string|max:255',
        ]);

        $productBrand = ProductBrand::find($productBrandId);
        if (!$productBrand) {
            return response()->json(['message' => 'Product brand not found'], 404);
        }

        $logoUrl = $productBrand->logo_url;
        if ($request->hasFile('logo')) {
            if ($logoUrl) {
                $photoPath = parse_url($logoUrl, PHP_URL_PATH);
                $photoPath = ltrim($photoPath, '/');
                if (file_exists(public_path($photoPath))) {
                    unlink(public_path($photoPath));
                }
            }
            $logoUrl = $this->uploadPhoto($request->file('logo'), 'product_brand_logos');
        }

        $productBrand->update([
            'name' => $request->name,
            'description' => $request->description,
            'logo_url' => $logoUrl,
            'country_of_origin' => $validatedData['country_of_origin'],
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Product brand updated successfully', 'data' => $productBrand]);
    }

    // Remove the specified product brand
    public function destroy($id)
    {
        $productBrand = ProductBrand::find($id);

        if (!$productBrand) {
            return response()->json(['message' => 'Product brand not found'], 404);
        }

        if ($productBrand->logo_url) {
            $photoPath = parse_url($productBrand->logo_url, PHP_URL_PATH);
            $photoPath = ltrim($photoPath, '/');
            if (file_exists(public_path($photoPath))) {
                unlink(public_path($photoPath));
            }
        }

        $productBrand->delete();

        return response()->json(null, 204);
    }
}

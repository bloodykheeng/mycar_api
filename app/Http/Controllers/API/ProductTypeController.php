<?php

namespace App\Http\Controllers\API;

use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProductTypeController extends Controller
{
    // Display a listing of product types
    public function index()
    {
        $productTypes = ProductType::with(['creator', 'updater'])->get();
        return response()->json($productTypes);
    }

    // Store a newly created product type
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|max:255',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = $this->uploadPhoto($request->file('photo'), 'product_type_photos');
        }

        $productType = ProductType::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'status' => $validatedData['status'],
            'photo_url' => $photoUrl,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Product type created successfully', 'data' => $productType]);
    }

    // Display the specified product type
    public function show($id)
    {
        $productType = ProductType::with(['creator', 'updater'])->find($id);

        if (!$productType) {
            return response()->json(['message' => 'Product type not found'], 404);
        }

        return response()->json($productType);
    }

    // Update the specified product type
    public function update(Request $request, $id)
    {
        $productType = ProductType::find($id);
        if (!$productType) {
            return response()->json(['message' => 'Product type not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|max:255',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoUrl = $productType->photo_url;
        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($photoUrl) {
                $this->deletePhoto($photoUrl);
            }
            $photoUrl = $this->uploadPhoto($request->file('photo'), 'product_type_photos');
        }

        $productType->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'status' => $request->status,
            'photo_url' => $photoUrl,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Product type updated successfully', 'data' => $productType]);
    }

    // Remove the specified product type
    public function destroy($id)
    {
        $productType = ProductType::find($id);

        if (!$productType) {
            return response()->json(['message' => 'Product type not found'], 404);
        }

        // Delete associated photo file
        if ($productType->photo_url) {
            $this->deletePhoto($productType->photo_url);
        }

        $productType->delete();

        return response()->json(['message' => 'Product type deleted successfully']);
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

    private function deletePhoto($photoUrl)
    {
        $photoPath = parse_url($photoUrl, PHP_URL_PATH);
        $photoPath = public_path($photoPath);
        if (File::exists($photoPath)) {
            File::delete($photoPath);
        }
    }
}
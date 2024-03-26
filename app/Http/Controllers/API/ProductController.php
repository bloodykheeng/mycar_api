<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\ProductPhoto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['brand', 'vendor', 'createdBy', 'updatedBy'])->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'mileage' => 'nullable|integer',
            'number_plate' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'color' => 'nullable|string|max:255',
            'quantity' => 'required|integer',
            'product_brand_id' => 'required|exists:product_brands,id',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $product = Product::create($validated);

        // Handle Photos
        if ($request->hasFile('files')) {
            $images = $request->file('files');
            $captions = $request->input('imagesWithCaptions', []);

            foreach ($images as $index => $image) {
                $photoUrl = $this->uploadPhoto($image, 'product_photos');
                $caption = $captions[$index]['caption'] ?? '';

                ProductPhoto::create([
                    'product_id' => $product->id,
                    'photo_url' => $photoUrl,
                    'caption' => $caption,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }


        return response()->json(['message' => 'Product created successfully', 'data' => $product]);
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

    public function show($id)
    {
        $product = Product::with(['brand', 'vendor', 'createdBy', 'updatedBy'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'mileage' => 'nullable|integer',
            'number_plate' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'color' => 'nullable|string|max:255',
            'quantity' => 'required|integer',
            'product_brand_id' => 'required|exists:product_brands,id',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $validated['updated_by'] = Auth::id();

        $product->update($validated);

        // Handle new photo uploads
        if ($request->hasFile('files')) {
            $images = $request->file('files');
            $captions = $request->input('imagesWithCaptions', []);

            // Delete existing photos and their files
            foreach ($product->photos as $photo) {
                $photoPath = parse_url($photo->photo_url, PHP_URL_PATH);
                $photoPath = ltrim($photoPath, '/');
                if (file_exists(public_path($photoPath))) {
                    unlink(public_path($photoPath));
                }
                $photo->delete();
            }

            foreach ($images as $index => $image) {
                $photoUrl = $this->uploadPhoto($image, 'product_photos');
                $caption = $captions[$index]['caption'] ?? '';

                ProductPhoto::create([
                    'product_id' => $product->id,
                    'photo_url' => $photoUrl,
                    'caption' => $caption,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }

        return response()->json(['message' => 'Product updated successfully', 'data' => $product]);
    }

    public function destroy($id)
    {
        $product = Product::with('photos')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Delete associated photos
        foreach ($product->photos as $photo) {
            $photoPath = parse_url($photo->photo_url, PHP_URL_PATH);
            $photoPath = ltrim($photoPath, '/');

            if (file_exists(public_path($photoPath))) {
                unlink(public_path($photoPath));
            }

            $photo->delete();
        }

        $product->delete();

        return response()->json(null, 204);
    }
}

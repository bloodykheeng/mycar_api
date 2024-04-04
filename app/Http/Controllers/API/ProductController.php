<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\ProductVideo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['brand', 'photos', 'videos', 'type', 'vendor', 'createdBy', 'updatedBy'])->get();
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
            'product_type_id' => 'required|exists:product_types,id',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $product = Product::create($validated);

        // Handle Photos
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $imageCaptions = $request->input('imageCaptions', []);

            foreach ($images as $index => $image) {
                $photoUrl = $this->uploadPhoto($image, 'product_photos');
                $caption = $imageCaptions[$index] ?? '';

                ProductPhoto::create([
                    'product_id' => $product->id,
                    'photo_url' => $photoUrl,
                    'caption' => $caption,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }

        // Handle Videos
        if ($request->hasFile('videos')) {
            $videos = $request->file('videos');
            $videoCaptions = $request->input('videoCaptions', []);

            foreach ($videos as $index => $video) {
                $videoUrl = $this->uploadPhoto($video, 'product_videos'); // You may need a different method for videos
                $caption = $videoCaptions[$index] ?? '';

                ProductVideo::create([
                    'product_id' => $product->id,
                    'video_url' => $videoUrl,
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
        $product = Product::with(['brand', 'type', 'photos', 'videos', 'vendor', 'createdBy', 'updatedBy'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::with('photos', 'videos')->find($id);

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
            'product_type_id' => 'required|exists:product_types,id',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $validated['updated_by'] = Auth::id();

        $product->update($validated);

        // Delete existing photos
        foreach ($product->photos as $photo) {
            $this->deleteFile($photo->photo_url);
            $photo->delete();
        }

        // Delete existing videos
        foreach ($product->videos as $video) {
            $this->deleteFile($video->video_url);
            $video->delete();
        }
        // Handle new photo uploads
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $imageCaptions = $request->input('imageCaptions', []);

            foreach ($images as $index => $image) {
                $photoUrl = $this->uploadPhoto($image, 'product_photos');
                $caption = $imageCaptions[$index] ?? '';

                ProductPhoto::create([
                    'product_id' => $product->id,
                    'photo_url' => $photoUrl,
                    'caption' => $caption,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }

        // Handle new video uploads
        if ($request->hasFile('videos')) {
            $videos = $request->file('videos');
            $videoCaptions = $request->input('videoCaptions', []);

            foreach ($videos as $index => $video) {
                $videoUrl = $this->uploadPhoto($video, 'product_videos'); // Adjust if you have a different method for videos
                $caption = $videoCaptions[$index] ?? '';

                ProductVideo::create([
                    'product_id' => $product->id,
                    'video_url' => $videoUrl,
                    'caption' => $caption,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }

        return response()->json(['message' => 'Product updated successfully', 'data' => $product]);
    }


    private function deleteFile($filePath)
    {
        $path = parse_url($filePath, PHP_URL_PATH);
        $absolutePath = public_path($path);

        if (file_exists($absolutePath)) {
            unlink($absolutePath);
        }
    }

    public function destroy($id)
    {
        $product = Product::with('photos', 'videos')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Delete associated photos
        foreach ($product->photos as $photo) {
            $this->deleteFile($photo->photo_url);
            $photo->delete();
        }

        // Delete associated videos
        foreach ($product->videos as $video) {
            $this->deleteFile($video->video_url);
            $video->delete();
        }

        $product->delete();

        return response()->json(null, 204);
    }
}
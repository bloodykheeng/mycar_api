<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Garage;
use Illuminate\Support\Facades\File;

class GarageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $motorThirdParties = Garage::with(['createdByUser', 'updatedByUser', 'reviews'])->get();
        return response()->json($motorThirdParties);
    }

    // Store a newly created Garage
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'address' => 'required',
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'availability' => 'required',
            'opening_hours' => 'required',
            'special_features' => 'nullable',
        ]);

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = $this->uploadPhoto($request->file('photo'), 'garage_photos');
            $validatedData['photo_url'] = $photoUrl;
        }
        $validatedData['availability'] = $validatedData['availability'] ? 1 : 0;
        $validatedData['created_by'] = Auth::id();
        $validatedData['updated_by'] = Auth::id();

        $garage = Garage::create($validatedData);

        return response()->json(['message' => 'Garage created successfully', 'data' => $garage]);
    }

    // Display the specified Garage
    public function show($id)
    {
        $garage = Garage::with(['createdByUser', 'updatedByUser', 'reviews'])->find($id);

        if (!$garage) {
            return response()->json(['message' => 'Garage not found'], 404);
        }

        return response()->json($garage);
    }

    // Update the specified Garage
    public function update(Request $request, $id)
    {
        $garage = Garage::find($id);
        if (!$garage) {
            return response()->json(['message' => 'Garage not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required',
            'address' => 'required',
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'availability' => 'required',
            'opening_hours' => 'required',
            'special_features' => 'nullable',
        ]);

        $photoUrl = $garage->photo_url;
        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($photoUrl) {
                $this->deletePhoto($photoUrl);
            }
            $photoUrl = $this->uploadPhoto($request->file('photo'), 'garage_photos');
            $validatedData['photo_url'] = $photoUrl;
        }
        $validatedData['updated_by'] = Auth::id();
        $validatedData['availability'] = $validatedData['availability'] ? 1 : 0;

        $garage->update($validatedData);

        return response()->json(['message' => 'Garage updated successfully', 'data' => $garage]);
    }

    // Remove the specified Garage
    public function destroy($id)
    {
        $garage = Garage::find($id);

        if (!$garage) {
            return response()->json(['message' => 'Garage not found'], 404);
        }

        // Delete associated photo file
        if ($garage->photo_url) {
            $this->deletePhoto($garage->photo_url);
        }

        $garage->delete();

        return response()->json(['message' => 'Garage deleted successfully']);
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
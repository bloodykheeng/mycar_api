<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SparePart;
use Illuminate\Support\Facades\File;

class SparePartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $spareParts = SparePart::with(['vendor', 'createdBy', 'updatedBy'])->get();
        return response()->json($spareParts);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = $this->uploadPhoto($request->file('photo'), 'spare_part_photos'); // Save the photo in a specific folder
            $validated['photo_url'] = $photoUrl;
        }

        $sparePart = SparePart::create($validated);
        return response()->json($sparePart, 201);
    }

    public function show($id)
    {

        $sparePart = SparePart::with(['vendor', 'createdBy', 'updatedBy'])->find($id);

        if (!$sparePart) {
            return response()->json(['message' => 'Spare Part not found'], 404);
        }

        return response()->json($sparePart);
    }

    public function update(Request $request, $id)
    {
        $sparePart = SparePart::find($id);
        if (!$sparePart) {
            return response()->json(['message' => 'Spare Part not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $validated['updated_by'] = Auth::id();

        $photoUrl = $sparePart->photo_url;
        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($photoUrl) {
                $this->deletePhoto($photoUrl);
            }
            $photoUrl = $this->uploadPhoto($request->file('photo'), 'spare_part_photos');
            $validated['photo_url'] = $photoUrl;
        }

        $sparePart->update($validated);
        return response()->json($sparePart);
    }

    public function destroy($id)
    {
        $sparePart = SparePart::find($id);

        if (!$sparePart) {
            return response()->json(['message' => 'Spare Part not found'], 404);
        }

        $sparePart->delete();

        return response()->json(null, 204); // No content to indicate successful deletion
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
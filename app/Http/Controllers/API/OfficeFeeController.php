<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OfficeFee;
use Illuminate\Support\Facades\File;

class OfficeFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $officeFees = OfficeFee::with(['createdBy', 'updatedBy'])->get();
        return response()->json($officeFees);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'service_description' => 'required|string|max:255',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'fee_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:6', // Assuming currency code is 3 characters long
            'payment_terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'room_capacity' => 'nullable|integer|min:0',
            'billing_cycle' => 'required|string|in:monthly,weekly,quarterly,annually',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $photoUrl = null;
        if ($request->hasFile('photo_url')) {
            $photoUrl = $this->uploadPhoto($request->file('photo_url'), 'office_photos'); // Save the photo in a specific folder
            $validated['photo_url'] = $photoUrl;
        }

        $officeFee = OfficeFee::create($validated);
        return response()->json($officeFee, 201);
    }

    public function show($id)
    {

        $officeFee = OfficeFee::with(['createdBy', 'updatedBy'])->find($id);

        if (!$officeFee) {
            return response()->json(['message' => 'Office Fee not found'], 404);
        }

        return response()->json($officeFee);
    }

    public function update(Request $request, $id)
    {
        $officeFee = OfficeFee::find($id);
        if (!$officeFee) {
            return response()->json(['message' => 'Office Fee not found'], 404);
        }

        $validated = $request->validate([
            'service_description' => 'required|string|max:255',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'fee_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:6', // Assuming currency code is 3 characters long
            'payment_terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'room_capacity' => 'nullable|integer|min:0',
            'billing_cycle' => 'required|string|in:monthly,weekly,quarterly,annually',
        ]);

        $validated['updated_by'] = Auth::id();

        $photoUrl = $officeFee->photo_url;
        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($photoUrl) {
                $this->deletePhoto($photoUrl);
            }
            $photoUrl = $this->uploadPhoto($request->file('photo'), 'office_photos');
            $validated['photo_url'] = $photoUrl;
        }

        $officeFee->update($validated);
        return response()->json($officeFee);
    }

    public function destroy($id)
    {
        $officeFee = OfficeFee::find($id);

        if (!$officeFee) {
            return response()->json(['message' => 'Office Fee not found'], 404);
        }

        $officeFee->delete();

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
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
    public function index(Request $request)
    {
        // Start building the query
        $query = SparePart::with(['sparePartType', 'vendor', 'createdBy', 'updatedBy']);

        // Get the currently authenticated user
        /** @var \App\Models\User */
        $user = Auth::user();

        // Check if the user has the 'Vendor' role and apply the filter
        if (isset($user) && $user->hasRole('Vendor')) {
            // Assuming the UserVendor model defines the relationship to get the vendor id
            $vendorId = $user->vendors->vendor_id ?? null;
            if ($vendorId) {
                $query->where('vendor_id', $vendorId);
            }
        }

        // Apply filters from request
        if (!empty($request->search)) { // Check if search is not null and not an empty string
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if (!empty($request->condition)) { // Check if condition is not null and not an empty string
            $query->where('condition', $request->condition);
        }

        if (!empty($request->maxPrice)) { // Check if maxPrice is not null and not an empty string
            $query->where('price', '<=', $request->maxPrice);
        }

        if (!empty($request->spare_part_type)) { // Check if car_type is not null and not an empty string
            // Assuming `car_type` is the slug of the type
            $query->whereHas('sparePartType', function ($q) use ($request) {
                $q->where('slug', $request->spare_part_type);
            });
        }

        // Execute the query and get the results
        $spareParts = $query->get();

        return response()->json($spareParts);
    }

    // New function to get car by slug
    public function getBySlug($slug)
    {
        // Retrieve the car along with related details
        $car = SparePart::with(['sparePartType', 'vendor', 'createdBy', 'updatedBy'])->where('slug', $slug)
            ->first();

        if (!$car) {
            return response()->json(['message' => 'Spare part not found'], 404);
        }

        return response()->json($car);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
            'condition' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'vendor_id' => 'required|exists:vendors,id',
            'spare_part_type_id' => 'required|exists:spare_part_types,id',
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


        $sparePart = SparePart::with(['sparePartType', 'vendor', 'createdBy', 'updatedBy'])->find($id);

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
            // 'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
            'condition' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'vendor_id' => 'required|exists:vendors,id',
            'spare_part_type_id' => 'required|exists:spare_part_types,id',
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
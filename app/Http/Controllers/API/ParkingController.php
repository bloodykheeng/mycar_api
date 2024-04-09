<?php

namespace App\Http\Controllers\API;

use App\Models\Parking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ParkingController extends Controller
{
    public function index()
    {
        $parking = Parking::with(['car',  'vendor', 'createdBy', 'updatedBy'])->get();
        return response()->json($parking);
    }

    public function show($id)
    {
        $parking = Parking::with(['car',  'vendor', 'createdBy', 'updatedBy'])->find($id);
        if (!$parking) {
            return response()->json(['message' => 'Parking not found'], 404);
        }
        return response()->json($parking);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:products,id',
            'currency' => 'required|string|max:3',
            'billing_cycle' => 'required|string',
            'status' => 'required|string',
            'fee_amount' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'details' => 'nullable|string',
            'vendor_id' => 'nullable|exists:vendors,id', // Assuming vendor is optional
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $parking = Parking::create($validated);
        return response()->json(['message' => 'Parking created successfully', 'data' => $parking], 201);
    }


    public function update(Request $request, $id)
    {
        $parking = Parking::find($id);
        if (!$parking) {
            return response()->json(['message' => 'Parking not found'], 404);
        }

        $validated = $request->validate([
            'car_id' => 'sometimes|exists:products,id',
            'vendor_id' => 'sometimes|exists:vendors,id',
            'currency' => 'sometimes|string|max:255',
            'billing_cycle' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|max:255',
            'fee_amount' => 'sometimes|numeric',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'details' => 'nullable',
        ]);

        $validated['updated_by'] = Auth::id();

        $parking->update($validated);
        return response()->json(['message' => 'Parking updated successfully', 'data' => $parking]);
    }

    public function destroy($id)
    {
        $parking = Parking::find($id);
        if (!$parking) {
            return response()->json(['message' => 'Parking not found'], 404);
        }

        $parking->delete();
        return response()->json(null, 204);
    }
}

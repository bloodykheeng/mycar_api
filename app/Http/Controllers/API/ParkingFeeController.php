<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ParkingFee;

class ParkingFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parkingFees = ParkingFee::with(['createdBy', 'updatedBy'])->get();
        return response()->json($parkingFees);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'fee_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:6',
            'billing_cycle' => 'required|string|in:daily',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $officeFee = ParkingFee::create($validated);
        return response()->json($officeFee, 201);
    }

    public function show($id)
    {

        $officeFee = ParkingFee::with(['createdBy', 'updatedBy'])->find($id);

        if (!$officeFee) {
            return response()->json(['message' => 'Parking Fee not found'], 404);
        }

        return response()->json($officeFee);
    }

    public function update(Request $request, $id)
    {
        $officeFee = ParkingFee::find($id);
        if (!$officeFee) {
            return response()->json(['message' => 'Parking Fee not found'], 404);
        }

        $validated = $request->validate([
            'fee_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:6',
            'billing_cycle' => 'required|string|in:daily',
        ]);

        $validated['updated_by'] = Auth::id();

        $officeFee->update($validated);
        return response()->json($officeFee);
    }

    public function destroy($id)
    {
        $officeFee = ParkingFee::find($id);

        if (!$officeFee) {
            return response()->json(['message' => 'Parking Fee not found'], 404);
        }

        $officeFee->delete();

        return response()->json(null, 204); // No content to indicate successful deletion
    }

    
}
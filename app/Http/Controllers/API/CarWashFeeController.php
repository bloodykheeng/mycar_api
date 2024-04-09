<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CarWashFee;

class CarWashFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $careWashFees = CarWashFee::with(['createdBy', 'updatedBy', 'productType'])->get();
        return response()->json($careWashFees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fee_amount' => 'required|numeric',
            'product_type_id' => 'required|exists:product_types,id',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $careWashFee = CarWashFee::create($validated);
        return response()->json($careWashFee, 201);
    }

    public function show(CarWashFee $careWashFee)
    {
        $careWashFee->load(['createdBy', 'updatedBy', 'productType']);
        return response()->json($careWashFee);
    }

    public function update(Request $request, CarWashFee $careWashFee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fee_amount' => 'required|numeric',
            'product_type_id' => 'required|exists:product_types,id',
        ]);

        $validated['updated_by'] = Auth::id();

        $careWashFee->update($validated);
        return response()->json($careWashFee);
    }

    public function destroy($id)
    {
        $careWashFee = CarWashFee::find($id);

        if (!$careWashFee) {
            return response()->json(['message' => 'Car Wash Fee not found'], 404);
        }

        $careWashFee->delete();

        return response()->json(null, 204); // No content to indicate successful deletion
    }
}

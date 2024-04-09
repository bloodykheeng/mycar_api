<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CarWashOrder;

class CarWashOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carWashOrders = CarWashOrder::with(['car', 'carWashFee', 'createdBy', 'updatedBy'])->get();
        return response()->json($carWashOrders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_wash_fee_id' => 'required|exists:car_wash_fees,id',
            'car_id' => 'required|exists:products,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'vendor_id' => 'nullable|exists:vendors,id',

        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $carWashOrder = CarWashOrder::create($validated);
        return response()->json($carWashOrder, 201);
    }

    public function show(CarWashOrder $carWashOrder)
    {
        $carWashOrder->load(['car', 'carWashFee', 'createdBy', 'updatedBy']);
        return response()->json($carWashOrder);
    }

    public function update(Request $request, CarWashOrder $carWashOrder)
    {
        $validated = $request->validate([
            'car_wash_fee_id' => 'required|exists:car_wash_fees,id',
            'car_id' => 'required|exists:products,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'vendor_id' => 'nullable|exists:vendors,id',

        ]);

        $validated['updated_by'] = Auth::id();

        $carWashOrder->update($validated);
        return response()->json($carWashOrder);
    }

    public function destroy($id)
    {
        $carWashOrder = CarWashOrder::find($id);

        if (!$carWashOrder) {
            return response()->json(['message' => 'Car Wash Order not found'], 404);
        }

        $carWashOrder->delete();

        return response()->json(null, 204); // No content to indicate successful deletion
    }
}

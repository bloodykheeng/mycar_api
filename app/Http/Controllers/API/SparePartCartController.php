<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\SparePartCart;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SparePartCartController extends Controller
{
    public function index(Request $request)
    {
        $query = SparePartCart::with(['sparePart', 'createdBy', 'updatedBy']);

        // Order the results by the created_at column in descending order (latest first)
        // $query->latest();

        if ($request->has('user_id')) {
            $query->where('created_by', $request->user_id);
        }

        $sparePartCarts = $query->get();
        return response()->json($sparePartCarts);
    }

    public function show($id)
    {
        $sparePartCart = SparePartCart::with(['sparePart', 'createdBy', 'updatedBy'])->find($id);
        if (!$sparePartCart) {
            return response()->json(['message' => 'Spare Part Cart not found'], 404);
        }
        return response()->json($sparePartCart);
    }

    public function syncSparePartCarts(Request $request)
    {
        $sparePartCartsData = $request->validate([
            'spare_part_carts' => 'array',
            'spare_part_carts.*.spare_part_id' => 'required|exists:spare_parts,id',
            'spare_part_carts.*.selected_quantity' => 'required|integer|min:1',
            'spare_part_carts.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $results = [];

            if (!empty($sparePartCartsData['spare_part_carts'])) {
                foreach ($sparePartCartsData['spare_part_carts'] as $data) {
                    $data['created_by'] = $data['updated_by'] = auth()->id();

                    $sparePartCart = SparePartCart::updateOrCreate(
                        [
                            'spare_part_id' => $data['spare_part_id'],
                            'created_by' => $data['created_by']
                        ],
                        $data
                    );

                    $sparePartCart->load([
                        'sparePart',
                        'createdBy',
                        'updatedBy'
                    ]);

                    $sparePartDetails = $sparePartCart->sparePart ? $sparePartCart->sparePart->toArray() : [];
                    $results[] = array_merge($sparePartDetails, [
                        'spare_part_id' => $sparePartCart->spare_part_id,
                        'selected_quantity' => $sparePartCart->selected_quantity,
                        'price' => $sparePartCart->price,
                    ]);
                }
            } elseif (empty($sparePartCartsData['spare_part_carts'])) {
                $sparePartCarts = SparePartCart::where('created_by', auth()->id())
                    ->with([
                        'sparePart',
                        'createdBy',
                        'updatedBy'
                    ])
                    ->get();

                $results = $sparePartCarts->map(function ($sparePartCart) {
                    $sparePartDetails = $sparePartCart->sparePart ? $sparePartCart->sparePart->toArray() : [];
                    return array_merge($sparePartDetails, [
                        'spare_part_id' => $sparePartCart->spare_part_id,
                        'selected_quantity' => $sparePartCart->selected_quantity,
                        'price' => $sparePartCart->price,
                    ]);
                });

                return response()->json(['message' => 'Spare part carts retrieved successfully', 'data' => $results], 200);
            }

            DB::commit();
            return response()->json(['message' => 'Spare part carts synchronized successfully', 'data' => $results], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error synchronizing spare part carts', 'error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'spare_part_id' => 'required|exists:spare_parts,id',
            'selected_quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);


        try {
            $validated['created_by'] = auth()->user()->id;
            $validated['updated_by'] = auth()->user()->id;

            DB::beginTransaction();

            $sparePartCart = new SparePartCart($validated);
            $sparePartCart->save();

            DB::commit();
            return response()->json(['message' => 'Spare Part Cart created successfully', 'data' => $sparePartCart], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating spare part cart', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // $sparePartCart = SparePartCart::find($id);
        $sparePartCart = SparePartCart::where('spare_part_id', $id)
            ->where('created_by', auth()->user()->id)
            ->first();

        if (!$sparePartCart) {
            return response()->json(['message' => 'Spare Part Cart not found'], 404);
        }

        $validated = $request->validate([
            'spare_part_id' => 'sometimes|required|exists:spare_parts,id',
            'selected_quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $validated['updated_by'] = auth()->user()->id;

        try {
            DB::beginTransaction();

            $sparePartCart->update($validated);

            DB::commit();
            return response()->json(['message' => 'Spare Part Cart updated successfully', 'data' => $sparePartCart]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating spare part cart', 'error' => $e->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        // $sparePartCart = SparePartCart::find($id);
        $sparePartCart = SparePartCart::where('spare_part_id', $id)
            ->where('created_by', auth()->user()->id)
            ->first();

        if (!$sparePartCart) {
            return response()->json(['message' => 'Spare Part Cart not found'], 404);
        }

        $sparePartCart->delete();
        return response()->json(['message' => 'Spare Part Cart deleted successfully'], 204);
    }
}
<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\SparePartsCart;
use App\Models\SparePartCartItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SparePartsCartController extends Controller
{
    public function index(Request $request)
    {
        $query = SparePartsCart::with([
            'items' => function ($query) {
                $query->with([
                    'sparePart',
                    'creator'
                ]);
            },
            'user',
            'updater'
        ]);

        // Check if 'user_id' is provided in the request and filter by it
        if ($request->has('user_id')) {
            $query->where('created_by', $request->user_id);
        }

        $sparePartsCarts = $query->get();
        return response()->json($sparePartsCarts);
    }


    public function getCartByUserId(Request $request)
    {
        $query = SparePartsCart::with([
            'items' => function ($query) {
                $query->with([
                    'sparePart',
                    'creator'
                ]);
            }, 'user', 'updater'
        ]);

        // Check if 'user_id' is provided in the request and filter by it
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $sparePartsCart = $query->first(); // Use first() to get the first matching cart

        if (!$sparePartsCart) {
            return response()->json(['message' => 'Spare Parts Cart not found'], 404);
        }

        return response()->json($sparePartsCart);
    }


    public function show($id)
    {
        $sparePartsCart = SparePartsCart::with(['items', 'user', 'updater'])->find($id);
        if (!$sparePartsCart) {
            return response()->json(['message' => 'Spare Parts Cart not found'], 404);
        }
        return response()->json($sparePartsCart);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items.*.spare_part_id' => 'required|exists:spare_parts,id',
            'items.*.selected_quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $cart = new SparePartsCart([
                'user_id' => $validated['user_id'],
                'updated_by' => $request->user()->id
            ]);
            $cart->save();

            // Store items associated with the cart
            foreach ($validated['items'] as $itemData) {
                $cartItem = new SparePartCartItem([
                    'spare_parts_cart_id' => $cart->id,
                    'spare_part_id' => $itemData['spare_part_id'],
                    'selected_quantity' => $itemData['selected_quantity'],
                    'price' => $itemData['price'],
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id
                ]);
                $cartItem->save();
            }

            DB::commit();

            return response()->json(['message' => 'Cart and items created successfully', 'data' => $cart], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating cart and items', 'error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $cart = SparePartsCart::with('items')->find($id);
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'items.*.id' => 'sometimes|required|exists:spare_part_cart_items,id',
            'items.*.spare_part_id' => 'required|exists:spare_parts,id',
            'items.*.selected_quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $cart->update([
                'user_id' => $validated['user_id'] ?? $cart->user_id,
                'updated_by' => $request->user()->id
            ]);

            // Update or create items associated with the cart
            foreach ($validated['items'] as $itemData) {
                $cartItem = $cart->items()->find($itemData['id'] ?? null);
                if ($cartItem) {
                    $cartItem->update([
                        'spare_part_id' => $itemData['spare_part_id'],
                        'selected_quantity' => $itemData['selected_quantity'],
                        'price' => $itemData['price'],
                        'updated_by' => $request->user()->id
                    ]);
                } else {
                    $cartItem = new SparePartCartItem([
                        'spare_parts_cart_id' => $cart->id,
                        'spare_part_id' => $itemData['spare_part_id'],
                        'selected_quantity' => $itemData['selected_quantity'],
                        'price' => $itemData['price'],
                        'created_by' => $request->user()->id,
                        'updated_by' => $request->user()->id
                    ]);
                    $cartItem->save();
                }
            }

            DB::commit();

            return response()->json(['message' => 'Cart and items updated successfully', 'data' => $cart]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating cart and items', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $sparePartsCart = SparePartsCart::find($id);
        if (!$sparePartsCart) {
            return response()->json(['message' => 'Spare Parts Cart not found'], 404);
        }

        $sparePartsCart->delete();
        return response()->json(null, 204);
    }
}

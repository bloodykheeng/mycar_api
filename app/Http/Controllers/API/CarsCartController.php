<?php

namespace App\Http\Controllers\API;

use App\Models\CarsCart;
use App\Models\CarCartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CarsCartController extends Controller
{
    public function index(Request $request)
    {
        $query = CarsCart::with(['items' => function ($query) {
            $query->with([
                'car',
                'creator'
            ]);
        }, 'user', 'updater']);

        // Check if 'user_id' is provided in the request and filter by it
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $carsCarts = $query->get();
        return response()->json($carsCarts);
    }

    public function getCartByUserId(Request $request)
    {
        $query = CarsCart::with([
            'items' => function ($query) {
                $query->with([
                    'car' => function ($query) {
                        $query->with([
                            'brand',
                            'photos',
                            'videos',
                            'type',
                            'vendor',
                            'createdBy',
                            'updatedBy',
                            'inspector',
                            'carInspector.inspector',
                            'inspectionReport' => function ($query) {
                                $query->with([
                                    'CarInspectionReportCategory' => function ($query) {
                                        $query->with([
                                            'inspectionFieldCategory',
                                            'fields' => function ($query) {
                                                $query->with([
                                                    'inspectionField',
                                                    'creator'
                                                ]);
                                            }
                                        ]);
                                    },
                                    'car',
                                    'creator',
                                    'updater'
                                ]);
                            }
                        ]);
                    },
                    'creator'
                ]);
            },
            'user',
            'updater'
        ]);


        // Check if 'user_id' is provided in the request and filter by it
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $carsCart = $query->first(); // Use first() to get the first matching cart

        if (!$carsCart) {
            return response()->json();
            // return response()->json(['message' => 'Cars Cart not found'], 404);
        }

        return response()->json($carsCart);
    }

    public function show($id)
    {
        $carsCart = CarsCart::with(['items', 'user', 'updater'])->find($id);
        if (!$carsCart) {
            return response()->json(['message' => 'Cars Cart not found'], 404);
        }
        return response()->json($carsCart);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items.*.car_id' => 'required|exists:cars,id',
            'items.*.selected_quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $cart = new CarsCart([
                'user_id' => $validated['user_id'],
                'updated_by' => $request->user()->id
            ]);
            $cart->save();

            // Store items associated with the cart
            foreach ($validated['items'] as $itemData) {
                $cartItem = new CarCartItem([
                    'cars_cart_id' => $cart->id,
                    'car_id' => $itemData['car_id'],
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
        $cart = CarsCart::with('items')->find($id);
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'items.*.id' => 'sometimes|required|exists:car_cart_items,id',
            'items.*.car_id' => 'required|exists:cars,id',
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
            $updatedItemIds = [];
            foreach ($validated['items'] as $itemData) {
                $cartItem = $cart->items()->find($itemData['id'] ?? null);
                if ($cartItem) {
                    $cartItem->update([
                        'car_id' => $itemData['car_id'],
                        'selected_quantity' => $itemData['selected_quantity'],
                        'price' => $itemData['price'],
                        'updated_by' => $request->user()->id
                    ]);
                    $updatedItemIds[] = $cartItem->id; // Store updated item IDs
                } else {
                    $cartItem = new CarCartItem([
                        'cars_cart_id' => $cart->id,
                        'car_id' => $itemData['car_id'],
                        'selected_quantity' => $itemData['selected_quantity'],
                        'price' => $itemData['price'],
                        'created_by' => $request->user()->id,
                        'updated_by' => $request->user()->id
                    ]);
                    $cartItem->save();
                    $updatedItemIds[] = $cartItem->id; // Store newly created item IDs
                }
            }

            // Delete items that were not updated (they were not included in the request)
            $cart->items()->whereNotIn('id', $updatedItemIds)->delete();

            DB::commit();

            return response()->json(['message' => 'Cart and items updated successfully', 'data' => $cart]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating cart and items',
                'error' => $e->getMessage(),
                'line' => $e->getLine() // Include the line causing the error
            ], 500);
        }
    }

    public function destroy($id)
    {
        $cart = CarsCart::find($id);
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cart->delete();
        return response()->json(['message' => 'Cart deleted successfully'], 204);
    }
}

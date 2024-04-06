<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\GarageReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GarageReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'garage_id' => 'required|exists:garages,id',
            'comment' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $validatedData['user_id'] = Auth::id();

        $review = GarageReview::create($validatedData);
        return response()->json($review, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $review = GarageReview::find($id);
        if (!$review) {
            return response()->json(['message' => 'Garage not found'], 404);
        }

        $validatedData = $request->validate([
            'garage_id' => 'required|exists:garages,id',
            'comment' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $validatedData['user_id'] = Auth::id();

        $review->update($validatedData);
        return response()->json($review, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

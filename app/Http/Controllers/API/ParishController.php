<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parish;
use App\Models\SubCounty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Parishes",
 *     description="Endpoints for parishes"
 * )
 */
class ParishController extends Controller
{

    // GET /your-endpoint?subCounty_id=1
    public function index(Request $request)
    {
        $parishes = Parish::with('subCounty');

        // Assume no filters are set initially
        $noFilters = true;

        // Filter parishes by subCounty if provided
        if ($request->has('sub_county_id') && $request->input('sub_county_id')) {
            $parishes->where('sub_county_id', $request->input('sub_county_id'));
            $noFilters = false; // A filter has been applied
        }

        // Optionally filter the parishes by name
        if ($request->has('_q') && $request->input('_q')) {
            $parishes->where('name', 'like', '%' . $request->input('_q') . '%');
            $noFilters = false; // A filter has been applied
        }

        // If no filters are set, limit the results to the top 200 records
        if ($noFilters) {
            $parishes->limit(200);
        }

        return response()->json(['data' => $parishes->get()]);
    }




    public function show($id)
    {
        $parish = Parish::find($id);

        if (!$parish) {
            return response()->json(['message' => 'Parish not found'], 404);
        }

        return response()->json($parish);
    }


    public function showBySubcountyId($subcounty_id)
    {
        // Find the subcounty by ID
        $subcounty = SubCounty::find($subcounty_id);

        if (!$subcounty) {
            return response()->json(['message' => 'Subcounty not found'], 404);
        }

        // Get the parishes belonging to the subcounty
        $parishes = Parish::where('subcounty_id', $subcounty_id)->get();

        return response()->json($parishes);
    }




    public function store(Request $request)
    {
        // if (!Auth::user()->can('add parishes')) {
        //     return response()->json(['message' => 'You do not have permission to add parishes'], 403);
        // }

        $parishes = [];
        $existingParishes = [];
        $user = Auth::user();

        $rules = [
            'name' => 'required|string',
            'sub_county_id' => 'required|integer|exists:sub_counties,id',

        ];

        if (isset($request->all()[0])) {
            foreach ($request->all() as $data) {
                $validatedData = $request->validate($rules);

                if (Parish::where('name', $validatedData['name'])->exists()) {
                    $existingParishes[] = $validatedData['name'];
                    continue;
                }

                // $parish = Parish::create($validatedData);
                $parish = new Parish([
                    'name' => $validatedData['name'],
                    'sub_county_id' => $validatedData['sub_county_id'],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);

                $parish->save();
                $parishes[] = $parish;
            }

            if (count($existingParishes) > 0) {
                return response()->json(['message' => 'Some parishes already exist', 'existingParishes' => $existingParishes], 409);
            }
        } else {
            $validatedData = $request->validate($rules);

            if (Parish::where('name', $validatedData['name'])->exists()) {
                return response()->json(['message' => 'Parish ' . $validatedData['name'] . ' already exists'], 409);
            }

            // $parish = Parish::create($validatedData);
            $parish = new Parish([
                'name' => $validatedData['name'],
                'sub_county_id' => $validatedData['sub_county_id'],
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $parish->save();
            $parishes[] = $parish;
        }

        return response()->json(['message' => 'Parish(es) created successfully', 'data' => $parishes], 201);
    }





    public function update(Request $request, $id)
    {
        // if (!Auth::user()->can('edit parishes')) {
        //     return response()->json(['message' => 'You do not have permission to edit parishes'], 403);
        // }

        $parish = Parish::find($id);

        if (!$parish) {
            return response()->json(['message' => 'Parish not found'], 404);
        }

        $rules = [
            'name' => 'required|string|unique:parishes,name,' . $parish->id,
            'sub_county_id' => 'required|integer|exists:sub_counties,id',
        ];

        $validatedData = $request->validate($rules);
        $parish->update($validatedData);

        return response()->json(['message' => 'Parish updated successfully', 'data' => $parish]);
    }


    public function destroy($id)
    {
        // Check if the user has permission to delete parishes
        // if (!Auth::user()->can('delete parishes')) {
        //     return response()->json(['message' => 'You do not have permission to delete parishes'], 403);
        // }

        $ids = is_array($id) ? $id : [$id];
        $nonExistingParishes = [];

        foreach ($ids as $parishId) {
            $parish = Parish::find($parishId);

            if (!$parish) {
                $nonExistingParishes[] = $parishId;
            } else {
                $parish->delete();
            }
        }

        if (!empty($nonExistingParishes)) {
            return response()->json(['message' => 'Some parishes were not found', 'non_existing_parishes' => $nonExistingParishes], 404);
        }

        return response()->json(null, 204);
    }
}
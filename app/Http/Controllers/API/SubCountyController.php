<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\County;
use App\Models\SubCounty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="SubCounties",
 *     description="Endpoints for SubCounties"
 * )
 */
class SubCountyController extends Controller
{





    public function index(Request $request)
    {

        // Check if the user has permission to view counties
        // if (!Auth::user()->can('view subcounties')) {
        //     return response()->json(['message' => 'You do not have permission to view subcounties'], 403);
        // }
        // Assume no filters are set initially
        $noFilters = true;

        $subcounties = SubCounty::with('county');

        if ($request->has('county_id') && $request->input('county_id')) {
            $subcounties->where('county_id', $request->input('county_id'));
            $noFilters = false; // A filter has been applied
        }

        // You can add additional filters here and set $noFilters to false if they are applied

        // If no filters are set, limit the results to the top 200 records
        if ($noFilters) {
            $subcounties->limit(200);
        }

        return response()->json(['data' => $subcounties->get()]);
    }



    public function show($id)
    {
        $subCounty = SubCounty::find($id);

        if (!$subCounty) {
            return response()->json(['message' => 'SubCounty not found'], 404);
        }

        return response()->json($subCounty);
    }


    public function showByCounty($county_id)
    {
        $county = County::find($county_id);

        if (!$county) {
            return response()->json(['message' => 'County not found'], 404);
        }

        $subCounties = SubCounty::where('county_id', $county_id)->get();

        return response()->json(['data' => $subCounties]);
    }


    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'county_id' => 'required|exists:counties,id',
        ];

        $user = Auth::user();
        $subCounties = [];
        $existingSubCounties = [];

        if (isset($request->all()[0])) { // Bulk insert
            foreach ($request->all() as $data) {
                $validatedData = $request->validate($rules);

                if (SubCounty::where('name', $validatedData['name'])->where('county_id', $validatedData['county_id'])->exists()) {
                    $existingSubCounties[] = $validatedData['name'];
                    continue;
                }



                $subCounty = new SubCounty([
                    'name' => $validatedData['name'],
                    'county_id' => $validatedData['county_id'],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
                $subCounty->save();
                $subCounties[] = $subCounty;
            }

            if (count($existingSubCounties) > 0) {
                return response()->json(['message' => 'Some SubCounties already exist', 'existingSubCounties' => $existingSubCounties], 409);
            }
        } else { // Single insert
            $validatedData = $request->validate($rules);

            if (SubCounty::where('name', $validatedData['name'])->where('county_id', $validatedData['county_id'])->exists()) {
                return response()->json(['message' => 'SubCounty ' . $validatedData['name'] . ' already exists', 'existingSubCounty' => $validatedData['name']], 409);
            }


            $subCounty = new SubCounty([
                'name' => $validatedData['name'],
                'county_id' => $validatedData['county_id'],
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
            $subCounty->save();
            $subCounties[] = $subCounty;
        }

        return response()->json(['message' => 'SubCounty(s) created successfully', 'data' => $subCounties], 201);
    }

    public function update(Request $request, $id)
    {

        // Check if the user has permission to view counties
        // if (!Auth::user()->can('update subcounties')) {
        //     return response()->json(['message' => 'You do not have permission to update subcounties'], 403);
        // }
        $rules = [
            'name' => 'required|string|unique:sub_counties,name,' . $id,
            'county_id' => 'required|exists:counties,id',
        ];

        $validatedData = $request->validate($rules);
        $subCounty = SubCounty::find($id);

        if (!$subCounty) {
            return response()->json(['message' => 'SubCounty not found'], 404);
        }

        $subCounty->update($validatedData);

        return response()->json(['message' => 'SubCounty updated successfully', 'data' => $subCounty]);
    }

    public function destroy($id)
    {
        $subCounty = SubCounty::find($id);

        if (!$subCounty) {
            return response()->json(['message' => 'SubCounty not found'], 404);
        }

        $subCounty->delete();

        return response()->json(['message' => 'SubCounty deleted successfully']);
    }
}
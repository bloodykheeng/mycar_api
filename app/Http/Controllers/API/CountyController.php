<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\County;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Counties",
 *     description="Endpoints for counties"
 * )
 */
class CountyController extends Controller
{
    public function index(Request $request)
    {
        // Check if the user has permission to view counties
        // if (!Auth::user()->can('view counties')) {
        //     return response()->json(['message' => 'You do not have permission to view counties'], 403);
        // }

        $counties = County::with('district');

        // Initialize a flag to check if any filters have been applied
        $filtersApplied = false;

        if ($request->has('district_id') && $request->filled('district_id')) {
            $counties->where('district_id', $request->input('district_id'));
            $filtersApplied = true;
        }

        // If no filters have been applied, limit the results to the top 200 records
        if (!$filtersApplied) {
            $counties->limit(200);
        }

        return response()->json(['data' => $counties->get()]);
    }




    public function show($id)
    {
        $county = County::find($id);

        if (!$county) {
            return response()->json(['message' => 'County not found'], 404);
        }

        return response()->json($county);
    }

    public function showByDistrict($district_id)
    {
        $district = District::find($district_id);

        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }

        $counties = County::where('district_id', $district_id)->get();

        return response()->json(['data' => $counties]);
    }

    public function store(Request $request)
    {
        // Check if the user has permission to add counties
        // if (!Auth::user()->can('add counties')) {
        //     return response()->json(['message' => 'You do not have permission to add counties'], 403);
        // }

        $counties = [];
        $existingCounties = [];
        $user = Auth::user();

        $rules = [
            'name' => 'required|string',
            'district_id' => 'required|exists:districts,id',
        ];

        if (isset($request->all()[0])) {
            foreach ($request->all() as $data) {
                $validatedData = $request->validate($rules);

                if (County::where('name', $validatedData['name'])->where('district_id', $validatedData['district_id'])->exists()) {
                    $existingCounties[] = $validatedData['name'];
                    continue;
                }

                $county = new County([
                    'name' => $validatedData['name'],
                    'district_id' => $validatedData['district_id'],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);

                $county->save();
                $counties[] = $county;
            }
            if (count($existingCounties) > 0) {
                return response()->json(['message' => 'Some counties already exist', 'existingCounties' => $existingCounties], 409);
            }
        } else {
            $validatedData = $request->validate($rules);

            if (County::where('name', $validatedData['name'])->where('district_id', $validatedData['district_id'])->exists()) {
                return response()->json(['message' => 'County ' . $validatedData['name'] . ' already exists', 'existingCounty' => $validatedData['name']], 409);
            }

            $county = new County([
                'name' => $validatedData['name'],
                'district_id' => $validatedData['district_id'],
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $county->save();
            $counties[] = $county;
        }

        return response()->json(['message' => 'County(s) created successfully', 'data' => $counties], 201);
    }

    public function update(Request $request, $id)
    {
        // Check if the user has permission to edit counties
        // if (!Auth::user()->can('edit counties')) {
        //     return response()->json(['message' => 'You do not have permission to edit counties'], 403);
        // }

        $county = County::find($id);

        if (!$county) {
            return response()->json(['message' => 'County not found'], 404);
        }

        $rules = [
            'name' => 'required|string|unique:counties,name,' . $county->id,
            'district_id' => 'required|exists:districts,id',
        ];

        $validatedData = $request->validate($rules);

        $county->update($validatedData);

        return response()->json(['message' => 'County updated successfully', 'data' => $county]);
    }

    public function destroy($id)
    {
        $county = County::find($id);

        if (!$county) {
            return response()->json(['message' => 'County not found'], 404);
        }

        $county->delete();

        return response()->json(['message' => 'County deleted successfully']);
    }
}
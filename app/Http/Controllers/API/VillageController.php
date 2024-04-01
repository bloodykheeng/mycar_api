<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parish;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Villages",
 *     description="Endpoints for villages"
 * )
 */
class VillageController extends Controller
{
    public function index(Request $request)
    {

        // Check if any filter is set
        $filterSet = false;

        $villages = Village::with('parish');


        if ($request->has('parish_id') && $request->input('parish_id')) {
            $villages->where('parish_id', $request->input('parish_id'));
            $filterSet = true; // A filter has been applied
        }


        // If no filters are set, limit the results to the top 200 records
        if (!$filterSet) {
            $villages->limit(200);
        }

        return response()->json(['data' => $villages->get()]);
    }

    public function show($id)
    {
        $village = Village::find($id);

        if (!$village) {
            return response()->json(['message' => 'Village not found'], 404);
        }

        return response()->json($village);
    }

    public function showByParishId($parish_id)
    {
        $parish = Parish::find($parish_id);

        if (!$parish) {
            return response()->json(['message' => 'Parish not found'], 404);
        }

        $villages = Village::where('parish_id', $parish_id)->get();

        return response()->json(['data' => $villages]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'parish_id' => 'required|exists:parishes,id',
        ];

        $user = Auth::user();
        $villages = [];
        $existingVillages = [];

        if (isset($request->all()[0])) { // Bulk insert
            foreach ($request->all() as $data) {
                $validatedData = $request->validate($rules);

                if (Village::where('name', $validatedData['name'])->where('parish_id', $validatedData['parish_id'])->exists()) {
                    $existingVillages[] = $validatedData['name'];
                    continue;
                }

                $village = new Village([
                    'name' => $validatedData['name'],
                    'parish_id' => $validatedData['parish_id'],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
                $village->save();
                $villages[] = $village;
            }

            if (count($existingVillages) > 0) {
                return response()->json(['message' => 'Some Villages already exist', 'existingVillages' => $existingVillages], 409);
            }
        } else { // Single insert
            $validatedData = $request->validate($rules);

            if (Village::where('name', $validatedData['name'])->where('parish_id', $validatedData['parish_id'])->exists()) {
                return response()->json(['message' => 'Village ' . $validatedData['name'] . ' already exists', 'existingVillage' => $validatedData['name']], 409);
            }

            $village = new Village([
                'name' => $validatedData['name'],
                'parish_id' => $validatedData['parish_id'],
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
            $village->save();
            $villages[] = $village;
        }

        return response()->json(['message' => 'Village(s) created successfully', 'data' => $villages], 201);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|unique:villages,name,' . $id,
            'parish_id' => 'required|exists:parishes,id',
        ];

        $validatedData = $request->validate($rules);
        $village = Village::find($id);

        if (!$village) {
            return response()->json(['message' => 'Village not found'], 404);
        }

        $village->update($validatedData);
        return response()->json(['message' => 'Village updated successfully', 'data' => $village]);
    }

    public function destroy($id)
    {
        // Check if the user has permission to delete villages
        // if (!Auth::user()->can('delete villages')) {
        //     return response()->json(['message' => 'You do not have permission to delete villages'], 403);
        // }

        $ids = is_array($id) ? $id : [$id];
        $nonExistingVillages = [];

        foreach ($ids as $villageId) {
            $village = Village::find($villageId);

            if (!$village) {
                $nonExistingVillages[] = $villageId;
            } else {
                $village->delete();
            }
        }

        if (!empty($nonExistingVillages)) {
            return response()->json(['message' => 'Some villages were not found', 'non_existing_villages' => $nonExistingVillages], 404);
        }

        return response()->json(null, 204);
    }
}
<?php

namespace App\Http\Controllers\API;

use App\Models\County;
use App\Models\Parish;
use App\Models\Utility;
use App\Models\Village;
use App\Models\District;
use App\Models\SubCounty;
use App\Models\SubProject;
use Illuminate\Http\Request;
use App\Jobs\StoreSubLocationsJob;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DepartmentAdministration;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Districts",
 *     description="Endpoints for districts"
 * )
 */
class DistrictController extends Controller
{


    public function index(Request $request)
    {
        // Check if the user has permission to view districts
        // if (!Auth::user()->can('view districts')) {
        //     return response()->json(['message' => 'You do not have permission to view districts'], 403);
        // }

        $districtsQuery = District::query(); // Initialize the query builder for District

        // Initialize a flag to check if any filters have been applied
        $filtersApplied = false;

        if ($request->has('subproject_id') && $request->filled('subproject_id')) {
            // Find the subproject
            $subproject = SubProject::find($request->input('subproject_id'));

            if (!$subproject) {
                return response()->json(['message' => 'Subproject not found'], 404);
            }

            // Get the project of the subproject
            $project = $subproject->project;

            if (!$project) {
                return response()->json(['message' => 'Project not found for the given subproject'], 404);
            }

            // Change the base of the districts query to the districts associated with the project
            $districtsQuery = $project->districts();
            $filtersApplied = true; // A filter has been applied
        }

        // Filter by districtIds if present
        if ($request->has('districtIds') && is_array($request->input('districtIds'))) {
            $districtsQuery->whereIn('id', $request->input('districtIds'));
            $filtersApplied = true; // A filter has been applied
        }


        // Check if department_administration_id is present and valid
        if ($request->has('department_administration_id') && $request->filled('department_administration_id')) {
            // Find the DepartmentAdministration
            $departmentAdministration = DepartmentAdministration::find($request->input('department_administration_id'));

            if (!$departmentAdministration) {
                return response()->json(['message' => 'Department Administration not found'], 404);
            }

            // Change the base of the districts query to the districts associated with the DepartmentAdministration
            $districtsQuery = $departmentAdministration->districts();
        }


        // Check if utility_id is present and valid
        if ($request->has('utility_id') && $request->filled('utility_id')) {
            // Find the Utility
            $utility = Utility::find($request->input('utility_id'));

            if (!$utility) {
                return response()->json(['message' => 'Utility not found'], 404);
            }

            // Change the base of the districts query to the districts associated with the Utility
            $districtsQuery = $utility->districts();
        }




        // NEW: Filter by utility_id if present
        if ($request->has('utility_id') && $request->filled('utility_id')) {
            // Find the utility
            $utility = Utility::find($request->input('utility_id'));

            if (!$utility) {
                return response()->json(['message' => 'Utility not found'], 404);
            }

            // Change the base of the districts query to the districts associated with the utility
            $districtsQuery = $utility->districts();
            $filtersApplied = true; // A filter has been applied
        }

        // If no filters have been applied, limit the results to the top 200 records
        // if (!$filtersApplied) {
        //     $districtsQuery->limit(200);
        // }

        // Execute the query and get the results
        $districts = $districtsQuery->get();

        return response()->json(['data' => $districts]);
    }






    // public function storeSubLocations(Request $request)
    // {
    //     $subLocations = $request->json()->all(); // assuming the JSON is sent in the body of the request

    //     collect($subLocations)->chunk(100)->each(function ($locations) {
    //         DB::transaction(function () use ($locations) {
    //             foreach ($locations as $location) {
    //                 $districtName = ucfirst(strtolower($location['D']));
    //                 $countyName = ucfirst(strtolower($location['C']));
    //                 $subCountyName = ucfirst(strtolower($location['S']));
    //                 $parishName = ucfirst(strtolower($location['P']));
    //                 $villageName = ucfirst(strtolower($location['V']));

    //                 $district = District::firstOrCreate(['name' => $districtName]);
    //                 $county = County::firstOrCreate([
    //                     'name' => $countyName,
    //                     'district_id' => $district->id
    //                 ]);
    //                 $subCounty = SubCounty::firstOrCreate([
    //                     'name' => $subCountyName,
    //                     'county_id' => $county->id
    //                 ]);
    //                 $parish = Parish::firstOrCreate([
    //                     'name' => $parishName,
    //                     'sub_county_id' => $subCounty->id
    //                 ]);
    //                 Village::firstOrCreate([
    //                     'name' => $villageName,
    //                     'parish_id' => $parish->id
    //                 ]);
    //             }
    //         });
    //     });

    //     return response()->json(['message' => 'Sub locations stored successfully'], 200);
    // }

    public function storeSubLocations(Request $request)
    {
        $subLocations = $request->json()->all();
        // StoreSubLocationsJob::dispatch($subLocations);
        collect($subLocations)->chunk(1000)->each(function ($locationsChunk) {
            // Dispatch each chunk to a job
            StoreSubLocationsJob::dispatch($locationsChunk);
        });

        return response()->json(['message' => 'Sub locations are being processed'], 200);
    }

    public function show($id)
    {
        $district = District::find($id);

        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }

        return response()->json($district);
    }
    public function store(Request $request)
    {
        // Check if the user has permission to add districts
        // if (!Auth::user()->can('add districts')) {
        //     return response()->json(['message' => 'You do not have permission to add districts'], 403);
        // }

        $districts = [];
        $existingDistricts = [];
        $user = Auth::user();

        $rules = [
            'name' => 'required|string',
        ];

        if (isset($request->all()[0])) {
            // If it's an array of objects
            foreach ($request->all() as $data) {
                $validatedData = $request->validate($rules);

                // Check if district with this name already exists
                if (District::where('name', $validatedData['name'])->exists()) {
                    $existingDistricts[] = $validatedData['name'];
                    continue;
                }

                $district = new District([
                    'name' => $validatedData['name'],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);

                $district->save();
                $districts[] = $district;
            }
            if (count($existingDistricts) > 0) {
                return response()->json(['message' => 'Some districts already exist', 'existingDistricts' => $existingDistricts], 409);
            }
        } else {
            // If it's a single object
            $validatedData = $request->validate($rules);

            if (District::where('name', $validatedData['name'])->exists()) {
                return response()->json(['message' => 'District ' . $validatedData['name'] . ' already exists', 'existingDistrict' => $validatedData['name']], 409);
            }

            $district = new District([
                'name' => $validatedData['name'],
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $district->save();
            $districts[] = $district;
        }

        return response()->json(['message' => 'District(s) created successfully', 'data' => $districts], 201);
    }





    public function update(Request $request, $id)
    {
        // Check if the user has permission to edit districts
        // if (!Auth::user()->can('edit districts')) {
        //     return response()->json(['message' => 'You do not have permission to edit districts'], 403);
        // }

        // Find the district
        $district = District::find($id);

        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }

        $rules = [
            'name' => 'required|string',
        ];

        $validatedData = $request->validate($rules);

        $district->update($validatedData);

        return response()->json(['message' => 'District updated successfully', 'data' => $district]);
    }

    public function destroy($id)
    {
        $district = District::find($id);

        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }

        $district->delete();

        return response()->json(['message' => 'District deleted successfully']);
    }
}

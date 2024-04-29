<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\InspectionField;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class InspectionFieldController extends Controller
{
    // Display a listing of inspection fields
    public function index()
    {
        $inspectionFields = InspectionField::with(['creator', 'updater'])->get();
        return response()->json($inspectionFields);
    }

    // Store a newly created inspection field
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $inspectionField = InspectionField::create([
            'name' => $validatedData['name'],
            'field_type' => $validatedData['field_type'],
            'status' => $validatedData['status'],
            'description' => $validatedData['description'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Inspection field created successfully', 'data' => $inspectionField]);
    }

    // Display the specified inspection field
    public function show($id)
    {
        $inspectionField = InspectionField::with(['creator', 'updater'])->find($id);

        if (!$inspectionField) {
            return response()->json(['message' => 'Inspection field not found'], 404);
        }

        return response()->json($inspectionField);
    }

    // Update the specified inspection field
    public function update(Request $request, $id)
    {
        $inspectionField = InspectionField::find($id);
        if (!$inspectionField) {
            return response()->json(['message' => 'Inspection field not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $inspectionField->update([
            'name' => $validatedData['name'],
            'field_type' => $validatedData['field_type'],
            'status' => $validatedData['status'],
            'description' => $validatedData['description'],
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Inspection field updated successfully', 'data' => $inspectionField]);
    }

    // Remove the specified inspection field
    public function destroy($id)
    {
        $inspectionField = InspectionField::find($id);

        if (!$inspectionField) {
            return response()->json(['message' => 'Inspection field not found'], 404);
        }

        $inspectionField->delete();

        return response()->json(['message' => 'Inspection field deleted successfully']);
    }
}

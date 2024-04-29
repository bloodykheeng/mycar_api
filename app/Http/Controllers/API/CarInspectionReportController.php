<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CarInspectionReport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CarInspectionReportField;

class CarInspectionReportController extends Controller
{
    // Display a listing of all reports
    public function index()
    {
        $reports = CarInspectionReport::with(['fields.inspectionField', 'creator', 'updater'])->get();
        return response()->json($reports);
    }

    // Display a specific report by ID
    public function show($id)
    {
        $report = CarInspectionReport::with(['fields.inspectionField', 'creator', 'updater'])->find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        return response()->json($report);
    }


    // Store a newly created report and its fields
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'fields.*.inspection_fields_id' => 'required|exists:inspection_fields,id',
            'fields.*.value' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $report = CarInspectionReport::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            foreach ($validatedData['fields'] as $field) {
                CarInspectionReportField::create([
                    'car_inspection_reports_id' => $report->id,
                    'inspection_fields_id' => $field['inspection_fields_id'],
                    'value' => $field['value'],
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Report and its fields created successfully', 'data' => $report], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create the report', 'error' => $e->getMessage()], 400);
        }
    }

    // Update a report and its fields
    public function update(
        Request $request,
        $id
    ) {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'fields.*.id' => 'sometimes|exists:car_inspection_report_fields,id',
            'fields.*.inspection_fields_id' => 'required|exists:inspection_fields,id',
            'fields.*.value' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $report = CarInspectionReport::findOrFail($id);
            $report->update([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'updated_by' => Auth::id(),
            ]);

            foreach ($validatedData['fields'] as $field) {
                $reportField = CarInspectionReportField::updateOrCreate(
                    ['id' => $field['id'] ?? null],
                    [
                        'car_inspection_reports_id' => $report->id,
                        'inspection_fields_id' => $field['inspection_fields_id'],
                        'value' => $field['value'],
                        'updated_by' => Auth::id(),
                    ]
                );
            }

            DB::commit();
            return response()->json(['message' => 'Report and its fields updated successfully', 'data' => $report], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update the report', 'error' => $e->getMessage()], 400);
        }
    }


    // Remove a specific report and its related fields
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $report = CarInspectionReport::findOrFail($id);
            $report->fields()->delete();  // Delete all related fields first to maintain integrity
            $report->delete();           // Then delete the report

            DB::commit();
            return response()->json(['message' => 'Report deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete the report', 'error' => $e->getMessage()], 400);
        }
    }
}

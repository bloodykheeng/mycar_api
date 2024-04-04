<?php

namespace App\Http\Controllers\API;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with(['serviceType',  'vendor', 'createdBy', 'updatedBy'])->get();
        return response()->json($services);
    }


    public function getActiveServices()
    {
        $activeServices = Service::active()->with(['serviceType',  'vendor', 'createdBy', 'updatedBy'])->get();
        return response()->json($activeServices);
    }


    public function getInactiveServices()
    {
        $inactiveServices = Service::inactive()->with(['serviceType',  'vendor', 'createdBy', 'updatedBy'])->get();
        return response()->json($inactiveServices);
    }

    public function show($id)
    {
        $service = Service::with(['serviceType',  'vendor', 'createdBy', 'updatedBy'])->find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        return response()->json($service);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_type_id' => 'required|exists:service_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'service_fee' => 'required|numeric',
            'vendor_id' => 'required|exists:vendors,id',
            'details' => 'nullable',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $service = Service::create($validated);
        return response()->json(['message' => 'Service created successfully', 'data' => $service], 201);
    }

    public function update(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'service_type_id' => 'sometimes|exists:service_types,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'service_fee' => 'sometimes|numeric',
            'vendor_id' => 'sometimes|exists:vendors,id',
            'details' => 'nullable',
        ]);

        $validated['updated_by'] = Auth::id();

        $service->update($validated);
        return response()->json(['message' => 'Service updated successfully', 'data' => $service]);
    }

    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $service->delete();
        return response()->json(null, 204);
    }
}
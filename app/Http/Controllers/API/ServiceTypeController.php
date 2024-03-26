<?php

namespace App\Http\Controllers\API;

use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::with(['createdBy', 'updatedBy'])->get();
        return response()->json($serviceTypes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee' => 'required|numeric',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $serviceType = ServiceType::create($validated);
        return response()->json($serviceType, 201);
    }

    public function show(ServiceType $serviceType)
    {
        $serviceType->load(['createdBy', 'updatedBy']);
        return response()->json($serviceType);
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee' => 'required|numeric',
        ]);

        $validated['updated_by'] = Auth::id();

        $serviceType->update($validated);
        return response()->json($serviceType);
    }

    public function destroy($id)
    {
        $serviceType = ServiceType::find($id);

        if (!$serviceType) {
            return response()->json(['message' => 'Service Type not found'], 404);
        }

        $serviceType->delete();

        return response()->json(null, 204); // No content to indicate successful deletion
    }
}

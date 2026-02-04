<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleServiceController extends Controller
{
    public function index()
    {
        $services = VehicleService::with('vehicle')->orderBy('id', 'desc')->paginate(10);
        $vehicles = Vehicle::where('status', 'active')->get();
        return Inertia::render('Vehicle/Service', [
            'services' => $services,
            'vehicles' => $vehicles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'workshop_name' => 'nullable|string|max:255',
        ]);

        VehicleService::create($validated);

        return redirect()->back()->with('success', 'Service record created successfully.');
    }

    public function update(Request $request, VehicleService $service)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'workshop_name' => 'nullable|string|max:255',
        ]);

        $service->update($validated);

        return redirect()->back()->with('success', 'Service record updated successfully.');
    }

    public function destroy(VehicleService $service)
    {
        $service->delete();
        return redirect()->back()->with('success', 'Service record deleted successfully.');
    }
}

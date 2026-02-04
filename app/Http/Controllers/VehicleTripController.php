<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleTrip;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleTripController extends Controller
{
    public function index()
    {
        $trips = VehicleTrip::with('vehicle')->orderBy('id', 'desc')->paginate(10);
        $vehicles = Vehicle::where('status', 'active')->get();
        return Inertia::render('Vehicle/Trip', [
            'trips' => $trips,
            'vehicles' => $vehicles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_name' => 'nullable|string|max:255',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'purpose' => 'nullable|string|max:255',
            'status' => 'required|string|in:running,completed,cancelled',
            'income' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        VehicleTrip::create($validated);

        return redirect()->back()->with('success', 'Trip created successfully.');
    }

    public function update(Request $request, VehicleTrip $trip)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_name' => 'nullable|string|max:255',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'purpose' => 'nullable|string|max:255',
            'status' => 'required|string|in:running,completed,cancelled',
            'income' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $trip->update($validated);

        return redirect()->back()->with('success', 'Trip updated successfully.');
    }

    public function destroy(VehicleTrip $trip)
    {
        $trip->delete();
        return redirect()->back()->with('success', 'Trip deleted successfully.');
    }
}

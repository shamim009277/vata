<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleFuel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleFuelController extends Controller
{
    public function index()
    {
        $fuels = VehicleFuel::with('vehicle')->orderBy('id', 'desc')->paginate(10);
        $vehicles = Vehicle::where('status', 'active')->get();
        return Inertia::render('Vehicle/Fuel', [
            'fuels' => $fuels,
            'vehicles' => $vehicles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'fuel_type' => 'nullable|string|max:255',
            'voucher_no' => 'nullable|string|max:255',
        ]);

        VehicleFuel::create($validated);

        return redirect()->back()->with('success', 'Fuel record created successfully.');
    }

    public function update(Request $request, VehicleFuel $fuel)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'fuel_type' => 'nullable|string|max:255',
            'voucher_no' => 'nullable|string|max:255',
        ]);

        $fuel->update($validated);

        return redirect()->back()->with('success', 'Fuel record updated successfully.');
    }

    public function destroy(VehicleFuel $fuel)
    {
        $fuel->delete();
        return redirect()->back()->with('success', 'Fuel record deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleTrip;
use App\Models\VehicleFuel;
use App\Models\VehicleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function dashboard(Request $request)
    {
        $queryTrips = VehicleTrip::query();
        $queryFuel = VehicleFuel::query();
        $queryService = VehicleService::query();

        // Filter Logic
        if ($request->has('date') && $request->date) {
            $queryTrips->whereDate('start_time', $request->date);
            $queryFuel->whereDate('date', $request->date);
            $queryService->whereDate('date', $request->date);
        } elseif ($request->has('month') && $request->month) {
            $monthParts = explode('-', $request->month);
            if (count($monthParts) == 2) {
                $year = $monthParts[0];
                $month = $monthParts[1];
                $queryTrips->whereYear('start_time', $year)->whereMonth('start_time', $month);
                $queryFuel->whereYear('date', $year)->whereMonth('date', $month);
                $queryService->whereYear('date', $year)->whereMonth('date', $month);
            }
        } elseif ($request->has('filter_type')) {
            $filterType = $request->filter_type;

            if ($filterType == 'today') {
                $queryTrips->whereDate('start_time', today());
                $queryFuel->whereDate('date', today());
                $queryService->whereDate('date', today());
            } elseif ($filterType == 'last_7_days') {
                $queryTrips->whereDate('start_time', '>=', now()->subDays(7));
                $queryFuel->whereDate('date', '>=', now()->subDays(7));
                $queryService->whereDate('date', '>=', now()->subDays(7));
            } elseif ($filterType == 'last_15_days') {
                $queryTrips->whereDate('start_time', '>=', now()->subDays(15));
                $queryFuel->whereDate('date', '>=', now()->subDays(15));
                $queryService->whereDate('date', '>=', now()->subDays(15));
            }
        }

        $total_vehicles = Vehicle::count();
        $total_trips = $queryTrips->count();
        $total_income = $queryTrips->sum('income');
        $total_fuel_cost = $queryFuel->sum('amount');
        $total_service_cost = $queryService->sum('cost');
        $total_expense = $total_fuel_cost + $total_service_cost;

        return Inertia::render('Vehicle/Dashboard', [
            'stats' => compact('total_vehicles', 'total_trips', 'total_income', 'total_expense'),
            'filters' => $request->all(['filter_type', 'date', 'month'])
        ]);
    }

    public function index()
    {
        $vehicles = Vehicle::orderBy('id', 'desc')->paginate(10);
        return Inertia::render('Vehicle/Index', [
            'vehicles' => $vehicles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,maintenance,inactive',
        ]);

        Vehicle::create($validated);

        return redirect()->back()->with('success', 'Vehicle created successfully.');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,maintenance,inactive',
        ]);

        $vehicle->update($validated);

        return redirect()->back()->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->back()->with('success', 'Vehicle deleted successfully.');
    }
}

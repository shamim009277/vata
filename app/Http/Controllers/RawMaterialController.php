<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\RawMaterials;
use App\Models\Setting\Unit;
use Illuminate\Http\Request;
use App\Http\Requests\RowMaretialsRequest;

class RawMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RawMaterials::with('unit');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('cost_per_unit', 'like', "%{$search}%")
                  ->orWhere('stock_alert_quantity', 'like', "%{$search}%");

                if (strtolower($search) === 'active') {
                    $q->orWhere('is_active', true);
                } elseif (strtolower($search) === 'inactive') {
                    $q->orWhere('is_active', false);
                }
            })
            ->orWhereHas('unit', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->perPage ?? 10;
        $units = Unit::active()->select('id','name')->get();

        return Inertia::render('RawMaterial/Index', [
            'rawMaterials' => $query->orderBy('id', 'desc')->cursorPaginate($perPage)->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'perPage' => $perPage,
                'units' => $units,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RowMaretialsRequest $request)
    {
        try {
            $rawMaterial = RawMaterials::create($request->validated());
            return redirect()->route('raw-materials.index')->with('success', 'Raw Material created successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RowMaretialsRequest $request, string $id)
    {
        try {
            $rawMaterial = RawMaterials::findOrFail($id);
            $rawMaterial->update($request->validated());
            return redirect()->route('raw-materials.index')->with('success', 'Raw Material updated successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $rawMaterial = RawMaterials::findOrFail($id);
            $rawMaterial->delete();
            return redirect()->route('raw-materials.index')->with('success', 'Raw Material deleted successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function updateStatus($id)
    {
        try {
            $rawMaterial = RawMaterials::findOrFail($id);
            $rawMaterial->update(['is_active' => !$rawMaterial->is_active]);
            return redirect()->route('raw-materials.index')->with('success', 'Raw Material status updated successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}

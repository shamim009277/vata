<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Setting\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpParser\Node\Stmt\TryCatch;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Unit::query()
        ->select('*')
        ->selectRaw("
            CASE 
                WHEN unit_standards = 'W' THEN 'Weight'
                WHEN unit_standards = 'V' THEN 'Volume'
                WHEN unit_standards = 'L' THEN 'Length'
                WHEN unit_standards = 'Q' THEN 'Quantity'
                ELSE 'Unknown'
            END as standard_label
        ");
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('conversion_rate', 'like', "%{$search}%")
                  ->orWhere('root_id', 'like', "%{$search}%");

                if (strtolower($search) === 'active') {
                    $q->orWhere('is_active', true);
                } elseif (strtolower($search) === 'inactive') {
                    $q->orWhere('is_active', false);
                }
            });
        }

        $perPage = $request->perPage ?? 10;

        $unitStandards = ['W'=>'Weight', 'V'=>'Volume', 'L'=>'Length','Q'=>'Quantity'];
        $roots = Unit::where('is_root', 1)->where('root_id', null)->pluck('name', 'id');

        return Inertia::render('Unit/Index', [
            'units' => $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'perPage' => $perPage,
            ],
            'unitStandards' => $unitStandards,
            'roots' => $roots,
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
    public function store(UnitRequest $request)
    {
        try {
            $data = $request->validated();
            $data['is_root'] = $request->is_root ?? true;
            
            Unit::create($data);
            return redirect()->back()->with('success', 'Unit created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
    public function update(UnitRequest $request, string $id)
    {
        try {
            $unit = Unit::findOrFail($id);
            $data = $request->validated();
            $data['is_root'] = $request->is_root ?? true;
            $unit->update($data);
            return redirect()->back()->with('success', 'Unit created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $plan = Unit::findOrFail($id);
            $plan->delete();
    
            return redirect()->back();
        }catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong.'); 
        }
    }

    public function updateStatus(Request $request, Unit $unit)
    {
        $unit->update([
            'is_active' => $request->boolean('is_active')
        ]);
        return redirect()->back()->with('success', 'Status changed successfully.');
    }
}

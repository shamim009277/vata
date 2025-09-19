<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Module;
use Illuminate\Http\Request;
use App\Http\Requests\ModuleRequest;


class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Module::query();
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->perPage ?? 10;

        return Inertia::render('Module/Index', [
            'modules' => $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ModuleRequest $request)
    {
        try {
            $module = Module::create($request->validated());
            return redirect()->route('modules.index')->with('success', 'Module created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create module: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $module = Module::findOrFail($id);
        $module->delete();
        return redirect()->back()->with('success', 'Module deleted successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ModuleRequest $request, string $id)
    {
        try {
            $module = Module::findOrFail($id);
            $module->update($request->validated());
            return redirect()->route('modules.index')->with('success', 'Module updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update module: ' . $e->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(module $module)
    {
        $module->delete();
        return redirect()->back()->with('success', 'Module deleted successfully.');
    }

    public function updateStatus(Request $request, Module $module)
    {
        $module->update([
            'is_active' => $request->boolean('is_active')
        ]);
        return redirect()->back()->with('success', 'Status changed successfully.');
    }
}

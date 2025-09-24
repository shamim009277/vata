<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Inertia\Inertia;
use App\Models\Setting\Unit;
use Illuminate\Http\Request;
use App\Http\Requests\ItemRequest;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::with('unit')->query();

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

        return Inertia::render('Item/Index', [
            'items' => $query->orderBy('id', 'desc')->cursorPaginate($perPage)->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'perPage' => $perPage,
                'units' => $units,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemRequest $request)
    {
        try {
            $item = Item::create($request->validated());
            return redirect()->route('items.index')->with('success', 'Item created successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemRequest $request, string $id)
    {
        try {
            $item = Item::findOrFail($id);
            $item->update($request->validated());
            return redirect()->route('items.index')->with('success', 'Item updated successfully');
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
            $item = Item::findOrFail($id);
            $item->delete();
            return redirect()->route('items.index')->with('success', 'Item deleted successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function updateStatus($id)
    {
        try {
            $item = Item::findOrFail($id);
            $item->update(['is_active' => !$item->is_active]);
            return redirect()->route('items.index')->with('success', 'Item status updated successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}

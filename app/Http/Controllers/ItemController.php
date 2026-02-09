<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequest;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ItemController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:items.index', only: ['index']),
            new Middleware('permission:items.create', only: ['create', 'store']),
            new Middleware('permission:items.edit', only: ['edit', 'update', 'updateStatus']),
            new Middleware('permission:items.destroy', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::query();
        $search = $request->input('search');
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('rate', 'like', "%{$search}%");
        }
        $perPage = $request->perPage ?? 10;
        return Inertia::render('items/Index', [
            'items' => $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemRequest $request)
    {
        try {
            Item::create($request->validated());
            return redirect()->back()->with('success', 'আইটেম সফলভাবে তৈরি করা হয়েছে');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'আইটেম তৈরি সমস্যা আছে');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemRequest $request, $id)
    {
        try {
            Item::findOrFail($id)->update($request->validated());
            return redirect()->back()->with('success', 'আইটেম সফলভাবে আপডেট করা হয়েছে');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'আইটেম আপডেট সমস্যা আছে');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Item::findOrFail($id)->delete();
            return redirect()->back()->with('success', 'আইটেম সফলভাবে ডিলিট করা হয়েছে');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'আইটেম ডিলিট সমস্যা আছে');
        }
    }

    public function updateStatus(Request $request, string $id)
    {
        $item = Item::findOrFail($id);
        $item->update($request->all());
        return redirect()->back()->with('success', 'আইটেম সফলভাবে আপডেট করা হয়েছে');
    }
}

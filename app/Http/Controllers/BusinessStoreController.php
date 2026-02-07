<?php

namespace App\Http\Controllers;

use App\Models\BusinessStore;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusinessStoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $business_store = BusinessStore::where('user_id', auth()->user()->id)->first();
        return Inertia::render('business_store/Index', [
            'business_store' => $business_store
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
    public function store(Request $request)
    {
        $request->validate([
            'store_name'     => 'required|string|max:50',
            'short_name'     => 'nullable|string|max:50',
            'store_name_en'  => 'nullable|string|max:50',
            'address'        => 'required|string|max:100',
            'phone'          => 'required|string|max:50',
            'owner_name'     => 'required|string|max:50',
            'owner_phone'    => 'required|string|max:50',
        ]);

        $store = BusinessStore::updateOrCreate(
            ['id' => $request->id],
            [
                'store_name'    => $request->store_name,
                'short_name'    => $request->short_name,
                'store_name_en' => $request->store_name_en,
                'address'       => $request->address,
                'phone'         => $request->phone,
                'owner_name'    => $request->owner_name,
                'owner_phone'   => $request->owner_phone,
            ]
        );

        return redirect()->back()->with('success', 'Store updated successfully');
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

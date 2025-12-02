<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FieldList;
use App\Http\Requests\FieldRequest;
use Inertia\Inertia;

class FieldListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FieldList::query();
        $search = $request->input('search');
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        }
        $perPage = $request->perPage ?? 10;
        return Inertia::render('field_lists/Index', [
            'field_lists' => $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FieldRequest $request)
    {
        try {
            FieldList::create($request->validated());
            return redirect()->back()->with('success', 'মাঠ সফলভাবে তৈরি করা হয়েছে');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'মাঠ তৈরি সমস্যা আছে');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FieldRequest $request, string $id)
    {
        try {
            FieldList::findOrFail($id)->update($request->validated());
            return redirect()->back()->with('success', 'মাঠ সফলভাবে আপডেট করা হয়েছে');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'মাঠ আপডেট সমস্যা আছে');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            FieldList::findOrFail($id)->delete();
            return redirect()->back()->with('success', 'মাঠ সফলভাবে ডিলিট করা হয়েছে');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'মাঠ ডিলিট সমস্যা আছে');
        }
    }

    public function updateStatus(Request $request, string $id)
    {
        $field_list = FieldList::findOrFail($id);
        $field_list->update($request->all());
        return redirect()->back()->with('success', 'মাঠ সফলভাবে আপডেট করা হয়েছে');
    }
}

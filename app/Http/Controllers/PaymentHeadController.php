<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentHeadRequest;
use App\Models\PaymentHead;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PaymentHead::query();
        $search = $request->input('search');
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('group', 'like', "%{$search}%");
        }
        $perPage = $request->perPage ?? 10;
        return Inertia::render('payment_head/Index', [
            'heads' => $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'perPage' => $perPage,
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
    public function store(PaymentHeadRequest $request)
    {
        try {
            PaymentHead::create($request->validated());
            return redirect()->back()->with('success', 'খতিয়ান সফলভাবে তৈরি করা হয়েছে');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'খতিয়ান তৈরি সমস্যা আছে');
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
    public function update(PaymentHeadRequest $request, string $id)
    {
        try {
            PaymentHead::findOrFail($id)->update($request->validated());
            return redirect()->back()->with('success', 'খতিয়ান সফলভাবে আপডেট করা হয়েছে');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'খতিয়ান আপডেট সমস্যা আছে');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            PaymentHead::findOrFail($id)->delete();
            return redirect()->back()->with('success', 'খতিয়ান সফলভাবে ডিলিট করা হয়েছে');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'খতিয়ান ডিলিট সমস্যা আছে');
        }
    }

    public function groups(Request $request)
    {
        $search = $request->query('q', '');
        $groups = PaymentHead::query()
            ->where('group', 'like', "%{$search}%")
            ->limit(10)
            ->pluck('group');

        return response()->json($groups);
    }
}

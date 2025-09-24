<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerRequest;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();
        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");

                if (strtolower($search) === 'active') {
                    $q->orWhere('is_active', true);
                } elseif (strtolower($search) === 'inactive') {
                    $q->orWhere('is_active', false);
                }
            });
        }
        $perPage = $request->perPage ?? 10;

        return Inertia::render('Customer/Index', [
            'customers' => $query->orderBy('id', 'desc')->cursorPaginate($perPage)->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        try {
            $customer = Customer::create($request->validated());
            return redirect()->route('customers.index')->with('success', 'Customer created successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, string $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->update($request->validated());
            return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
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
            $customer = Customer::findOrFail($id);
            $customer->delete();
            return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function updateStatus($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->update(['is_active' => !$customer->is_active]);
            return redirect()->route('customers.index')->with('success', 'Customer status updated successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}

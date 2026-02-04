<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query()
            ->withSum('invoiceDetails as total_delivered', 'delivery_quantity')
            ->orderBy('id', 'desc');

        // Search filter
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->perPage ?? 10;
        $customers = $query->paginate($perPage)->withQueryString();

        return Inertia::render('customer/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::with(['invoices' => function($q) {
            $q->orderBy('id', 'desc');
        }, 'invoices.invoiceDetails.item', 'deliveries' => function($q) {
            $q->orderBy('id', 'desc');
        }, 'deliveries.item'])->findOrFail($id);
        
        return Inertia::render('customer/Show', [
            'customer' => $customer
        ]);
    }
}

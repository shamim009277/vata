<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Item;
use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\FieldList;
use Illuminate\Http\Request;
use App\Models\RowProduction;
use App\Http\Controllers\Controller;

class RowProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Active items list
        $fields = FieldList::active()->select('id','name')->get();
        $query = RowProduction::with([
            'field:id,name',
        ])->orderBy('id', 'desc');

        $search = $request->input('search');
        $date = $request->input('date');

        if ($date) {
            $query->whereDate('product_date', Carbon::parse($date)->format('Y-m-d'));
        } else {
            $query->whereDate('product_date', Carbon::now()->format('Y-m-d'));
        }


        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('field', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }
        // Pagination
        $perPage = $request->perPage ?? 10;

        // Render with Inertia
        return Inertia::render('row_productions/TodayProduction', [
            'productions' => $query->paginate($perPage)->withQueryString(),
            'fields' => $fields,
            'filters' => [
                'search' => $search,
                'date' => $date,
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
    public function store(Request $request)
    {
        //
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

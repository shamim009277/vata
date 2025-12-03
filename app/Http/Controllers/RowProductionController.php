<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Item;
use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\FieldList;
use Illuminate\Http\Request;
use App\Models\RowProduction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\RowProductionRequest;

class RowProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $fields = FieldList::active()->select('id', 'name')->get();
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

    public function allrowProduction(Request $request){
        //Active items list
        $fields = FieldList::active()->select('id', 'name')->get();
        $query = RowProduction::with([
            'field:id,name',
        ])->orderBy('id', 'desc');

        //Search filter
        $search = $request->input('search');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('field', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($start_date && $end_date) {
            $start = Carbon::parse($start_date)->startOfDay();
            $end = Carbon::parse($end_date)->endOfDay();
            $query->whereBetween('product_date', [$start, $end]);
        }

        //Pagination
        $perPage = $request->perPage ?? 10;

        //Render with Inertia
        return Inertia::render('row_productions/AllProduction', [
            'productions' => $query->paginate($perPage)->withQueryString(),
            'fields' => $fields,
            'filters' => [
                'search' => $search,
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
    public function store(RowProductionRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->id) {
                $production = RowProduction::find($request->id);
                if (!$production) {
                    throw new \Exception("Production not found");
                }

                $oldQty = $production->quantity;
                $fieldId = $production->field_list_id;

                $store = FieldList::find($fieldId);
                $store->update([
                    'stock_qty' => ($store->stock_qty - $oldQty) + $request->items[0]['quantity']
                ]);

                $production->update([
                    'product_date'   => $request->product_date,
                    'quantity'       => $request->items[0]['quantity'],
                    'note'           => $request->note,
                ]);
            }else {
                foreach ($request->items as $item) {
                    $created = RowProduction::create([
                        'season'         => 2425,
                        'field_list_id'  => $item['field_list_id'],
                        'product_date'   => $request->product_date,
                        'quantity'       => $item['quantity'],
                        'note'           => $request->note,
                    ]);

                    $store = FieldList::find($item['field_list_id']);
                    if (!$store) {
                        throw new \Exception("FieldList not found for ID " . $item['field_list_id']);
                    }
                    $store->update([
                        'stock_qty' => $store->stock_qty + $item['quantity'],
                    ]);
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'প্রোডাকশন সফল হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Production Create/Update Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);
            return redirect()->back()->with('error', 'প্রোডাকশন প্রক্রিয়াটি ব্যর্থ হয়েছে।');
        }
    }

    public function lockProduction(Request $request)
    {
        DB::beginTransaction();
        try {
            $production = RowProduction::where('product_date', $request->product_date)->update([
                'is_locked' => 1,
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'প্রোডাকশন লক করা হয়েছে।');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Production Lock Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);
            return redirect()->back()->with('error', 'প্রোডাকশন লক করতে ব্যর্থ হয়েছে।');
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $production = RowProduction::find($id);
            if (!$production) {
                throw new \Exception("Production not found for ID " . $id);
            }
            $store = FieldList::find($production->field_list_id);
            if (!$store) {
                throw new \Exception("FieldList not found for ID " . $production->field_list_id);
            }
            $store->update([
                'stock_qty' => $store->stock_qty - $production->quantity,
            ]);
            $production->delete();
            DB::commit();
            return redirect()->back()->with('success', 'প্রোডাকশন সফলভাবে মুছে ফেলা হয়েছে।');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Production Delete Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);
            return redirect()->back()->with('error', 'প্রোডাকশন মুছে ফেলতে ব্যর্থ হয়েছে।');
        }
    }
}

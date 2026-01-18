<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnloadRequest;
use App\Models\FieldList;
use App\Models\Item;
use App\Models\Load;
use App\Models\RowProduction;
use App\Models\Unload;
use App\Models\UnloadDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UnloadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = Item::active()->select('id', 'name')->get();
        $loads = Load::active()->select('loading_date','round','quantity')->get();
        $query = Unload::with([
            'details:unload_id,item_id,quantity',
            'details.item:id,name',
        ])->orderBy('id', 'desc');

        $search = $request->input('search');
        $date = $request->input('date');

        if ($date) {
            $query->whereDate('unload_date', Carbon::parse($date)->format('Y-m-d'));
        } else {
            $query->whereDate('unload_date', Carbon::now()->format('Y-m-d'));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('round', 'like', "%{$search}%")
                ->orWhere('round', 'like', "%{$search}%")
                ->orWhere('loading_date', 'like', "%{$search}%")
                ->orWhereHas('details', function ($q2) use ($search) {
                    $q2->whereHas('item', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });
                });
            });
        }
        // Pagination
        $perPage = $request->perPage ?? 10;

        //dd($query->paginate($perPage)->withQueryString());

        // Render with Inertia
        return Inertia::render('unload/Index', [
            'unloads' => $query->paginate($perPage)->withQueryString(),
            'items' => $items,
            'loads' => $loads,
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
    public function store(UnloadRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data = array_merge($data, [
                'season'        => 2425,
                'loading_date'  => Carbon::parse($request->load['loading_date'])->toDateString(),
                'load_quantity' => $request->load['total_quantity'],
                'round'         => $request->load['round'],
                'unload_date'   => Carbon::parse($request->unload_date)->toDateString(),
            ]);

            $unload = Unload::create($data);
            $details = collect($request->items)->map(function ($item) use ($unload) {
                return [
                    'unload_id'  => $unload->id,
                    'item_id'    => $item['item_id'],
                    'quantity'   => $item['quantity'],
                    'is_active'  => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            UnloadDetails::insert($details);
            DB::commit();

            return back()->with('success', 'আনলোডিং সফলভাবে তৈরি করা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Unload Create Failed', [
                'message' => $th->getMessage(),
                'line'    => $th->getLine(),
                'file'    => $th->getFile(),
            ]);

            return back()->with('error', 'আনলোডিং তৈরি করতে সমস্যা হয়েছে।');
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
    public function update(UnloadRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $unload = Unload::findOrFail($id);

            // ১. মূল unload update করা
            $data = $request->validated();
            $data = array_merge($data, [
                'season'        => 2425,
                'loading_date'  => Carbon::parse($request->load['loading_date'])->toDateString(),
                'load_quantity' => $request->load['total_quantity'],
                'round'         => $request->load['round'],
                'unload_date'   => Carbon::parse($request->unload_date)->toDateString(),
                'note'          => $request->note ?? null,
            ]);

            $unload->update($data);

            // ২. UnloadDetails update
            $existingIds = $unload->details()->pluck('id')->toArray();
            $requestIds  = collect($request->items)->pluck('id')->filter()->toArray(); // existing detail id থাকলে

            // ৩. Deleted items remove করা
            $deleteIds = array_diff($existingIds, $requestIds);
            if (!empty($deleteIds)) {
                UnloadDetails::whereIn('id', $deleteIds)->delete();
            }

            // ৪. Add/Update items
            foreach ($request->items as $item) {
                if (isset($item['id']) && $item['id']) {
                    // Update existing
                    UnloadDetails::where('id', $item['id'])->update([
                        'item_id'    => $item['item_id'],
                        'quantity'   => $item['quantity'],
                        'is_active'  => 1,
                        'updated_at' => now(),
                    ]);
                } else {
                    // Insert new
                    UnloadDetails::create([
                        'unload_id'  => $unload->id,
                        'item_id'    => $item['item_id'],
                        'quantity'   => $item['quantity'],
                        'is_active'  => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'আনলোডিং সফলভাবে আপডেট হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Unload Update Failed', [
                'message' => $th->getMessage(),
                'line'    => $th->getLine(),
                'file'    => $th->getFile(),
            ]);

            return back()->with('error', 'আনলোডিং আপডেট করতে সমস্যা হয়েছে।');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $unload = Unload::findOrFail($id);
            UnloadDetails::where('unload_id', $id)->delete();
            $unload->delete();

            DB::commit();
            return redirect()->back()->with('success', 'আনলোডিং সফলভাবে মুছে ফেলা হয়েছে।');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Unload Delete Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);
            return redirect()->back()->with('error', 'আনলোডিং মুছে ফেলতে ব্যর্থ হয়েছে।');
        }
    }
}

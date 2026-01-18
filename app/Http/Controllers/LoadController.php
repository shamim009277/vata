<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoadingRequest;
use App\Models\FieldList;
use App\Models\Item;
use App\Models\Load;
use App\Models\Round;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LoadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rounds = Round::active()
            ->select('id','round')
            ->when($request->q, function($query) use ($request) {
                $query->where('round', 'LIKE', "%{$request->q}%");
            })
            ->limit(10)
            ->get();


        $items = Item::active()->select('id','name')->get();
        $fields = FieldList::active()->select('id','name','stock_qty')->get();

        $query = Load::with([
            'item:id,name',
            'fieldList:id,name',
        ])->orderBy('id', 'desc');

        $date = $request->input('date');
        $round = $request->input('search_round');

        $query->when($date, function ($q) use ($date) {
            $q->whereDate('loading_date', Carbon::parse($date));
        });

        $query->when($round, function ($q) use ($round) {
            $q->where('round', 'LIKE', "%{$round}%");
        });

        if (!$date) {
            $query->whereDate('loading_date', Carbon::now());
        }
        $perPage = $request->perPage ?? 20;

        //dd($query->paginate($perPage)->withQueryString());

        return Inertia::render('load/Index',[
            'rounds'=>$rounds,
            'items'=>$items,
            'fields'=>$fields,
            'loads'=>$query->paginate($perPage)->withQueryString(),
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
    public function store(LoadingRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['season'] = 2425;
            $data['loading_date'] = Carbon::parse($request->loading_date)->format('Y-m-d');
            if ($data['load_type'] == 1) {
                $data['item_id'] = null;
            } else if ($data['load_type'] == 2) {
                $data['field_list_id'] = null;
            }
            Load::create($data);
            DB::commit();
            return redirect()->back()->with('success', 'লোডিং সফলভাবে তৈরি করা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Loading Create/Update Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);
            return redirect()->back()->with('error', 'লোডিং তৈরি সমস্যা আছে।');
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
    public function update(LoadingRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $load = Load::findOrFail($id);

            $data = $request->validated();
            $data['season'] = 2425;
            $data['loading_date'] = Carbon::parse($request->loading_date)->format('Y-m-d');

            // Load type অনুযায়ী clean data
            if ($data['load_type'] == 1) {
                $data['item_id'] = null;
            } else if ($data['load_type'] == 2) {
                $data['field_list_id'] = null;
            }

            // Update
            $load->update($data);

            DB::commit();
            return redirect()->back()->with('success', 'লোডিং সফলভাবে আপডেট করা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Loading Update Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);
            return redirect()->back()->with('error', 'লোডিং আপডেট করতে সমস্যা হয়েছে।');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $load = Load::find($id);
            $load->delete();
            DB::commit();
            return redirect()->back()->with('success', 'লোডিং সফলভাবে মুছে ফেলা হয়েছে।');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Production Delete Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);
            return redirect()->back()->with('error', 'লোডিং মুছে ফেলতে ব্যর্থ হয়েছে।');
        }
    }

    public function roundStore(Request $request){
        $request->validate([
            'round' => 'required|string|max:255'
        ]);
    
        Round::create([
            'round' => $request->round
        ]);
    
        return back()->with('success', 'Round inserted!');
    }


    public function checkStock(Request $request)
    {
        $request->validate([
            'load_type' => 'required|integer',
        ]);

        $stock = 0;

        if ($request->load_type == 1) {
            $stock = FieldList::where('id', $request->field_list_id)
                        ->value('stock_qty');
        }
        if ($request->load_type == 2) {
            $stock = Item::where('id', $request->item_id)
                        ->value('stock_qty');
        }

        return response()->json([
            'stock' => $stock ?? 0
        ]);
    }

}

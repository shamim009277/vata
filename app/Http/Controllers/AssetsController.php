<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetStockRequest;
use App\Http\Requests\IssueRequest;
use App\Models\AssetIssue;
use App\Models\AssetTransaction;
use App\Models\NewAssetStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AssetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = AssetTransaction::orderBy('id', 'desc');

        $stocks = NewAssetStock::with(['issues'])->get();
        return Inertia::render('asset/Dashboard', [
            'transactions' => $query->paginate(20)->withQueryString(),
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
    public function store(AssetStockRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $imagePath = null;
            if ($request->hasFile('photo')) {
                $imagePath = $request->file('photo')
                    ->store('assets/products', 'public');
            }

            if($request->has('has_warranty')) {
                $data['warranty_expiry_date'] = Carbon::parse($request->warranty_expiry_date)->format('Y-m-d');
            }

            $data['season'] = 2425;
            $data['photo'] = $imagePath;
            $data['stock_date'] = Carbon::now()->format('Y-m-d');
            $data['total_price'] = round($data['quantity'] * $data['unit_price']);

            NewAssetStock::create($data);

            // stock transaction
            $transaction = $request->validated();
            $transaction['date'] = Carbon::now()->format('Y-m-d');
            $transaction['status'] = '1'; // stock
            $transaction['season'] = 2425; // stock
            AssetTransaction::create($transaction);

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

    public function productsSearch(Request $request)
    {
        $q = $request->get('q');
        if (!$q) {
            return response()->json([]);
        }
        $items = NewAssetStock::query()
            ->select('id', 'product_name')
            ->where('product_name', 'like', "%{$q}%")
            ->orderBy('product_name')
            ->limit(10)
            ->get();
        return response()->json($items);
    }

    public function issueStore(IssueRequest $request)
    {
        try {
            $data = $request->validated();
            // product fetch from db
            $product = NewAssetStock::where('product_name', $data['product_name'])->first();
            if (!$product) {
                return redirect()->back()->with('error', 'প্রোডাক্ট পাওয়া যায়নি।');
            }

            if($product->quantity < $data['quantity']){
                return redirect()->back()->with('error', 'প্রোডাক্টের পরিমাণ পর্যাপ্ত নয়।');
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')
                    ->store('assets/products', 'public');
            }
            $data['season'] = Auth::user()->season;
            $data['stock_id'] = $product->id;
            $data['image'] = $imagePath;
            $data['to_whom'] = $request->name;
            $data['product_name'] = $request->product_name;
            $data['product_category'] = $product->product_category;
            $data['to_whom'] = $request->name;

            AssetIssue::create($data);

            // stock transaction
            $transaction = $request->validated();
            $transaction['date'] = $request->issue_date;
            $transaction['status'] = '2'; // stock
            $transaction['season'] = Auth::user()->season; // stock
            $transaction['product_category'] = $product->product_category;
            AssetTransaction::create($transaction);

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

        $data = $request->validated();
        $data['issue_date'] = Carbon::now()->format('Y-m-d');
        $data['season'] = 2425;
        NewAssetStock::find($data['asset_stock_id'])->issues()->create($data);
        return redirect()->back()->with('success', 'সফলভাবে ইস্যু করা হয়েছে!');
    }
}

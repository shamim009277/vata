<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetStockRequest;
use App\Http\Requests\IssueRequest;
use App\Models\AssetIssue;
use App\Models\AssetLost;
use App\Models\AssetTransaction;
use App\Models\IdelAsset;
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
    public function index(Request $request)
    {
        $filterType = $request->input('filter_type', 'all');
        $date = $request->input('date');
        $month = $request->input('month');

        $applyFilter = function ($query, $dateField) use ($filterType, $date, $month) {
            if ($date) {
                $query->whereDate($dateField, $date);
            } elseif ($month) {
                $query->whereYear($dateField, date('Y', strtotime($month)))
                      ->whereMonth($dateField, date('m', strtotime($month)));
            } elseif ($filterType === 'today') {
                $query->whereDate($dateField, Carbon::today());
            } elseif ($filterType === 'last_7_days') {
                $query->whereDate($dateField, '>=', Carbon::today()->subDays(7));
            } elseif ($filterType === 'last_15_days') {
                $query->whereDate($dateField, '>=', Carbon::today()->subDays(15));
            }
        };

        $transactionsQuery = AssetTransaction::orderBy('id', 'desc');
        $applyFilter($transactionsQuery, 'date');
        $transactions = $transactionsQuery->paginate(20)->withQueryString();

        $stocksQuery = NewAssetStock::with(['issues'])->orderBy('id', 'desc');
        $applyFilter($stocksQuery, 'stock_date');
        $stocks = $stocksQuery->paginate(20)->withQueryString();

        $issuesQuery = AssetIssue::with(['stock'])->orderBy('id', 'desc');
        $applyFilter($issuesQuery, 'issue_date');
        $issues = $issuesQuery->paginate(20)->withQueryString();

        $lostItemsQuery = AssetLost::with('issue')->orderBy('id', 'desc');
        $applyFilter($lostItemsQuery, 'lost_date');
        $lostItems = $lostItemsQuery->paginate(20)->withQueryString();

        // Calculate Stats with Filters
        $totalAssetQuery = NewAssetStock::query();
        $applyFilter($totalAssetQuery, 'stock_date');
        $totalAsset = $totalAssetQuery->sum('quantity');

        $lostAssetQuery = AssetLost::query();
        $applyFilter($lostAssetQuery, 'lost_date');
        $lostAsset = $lostAssetQuery->sum('quantity');

        $damageAssetQuery = IdelAsset::query();
        $applyFilter($damageAssetQuery, 'return_date');
        $damageAsset = $damageAssetQuery->sum('quantity');

        // Note: Present Asset calculation might be tricky with time filters.
        // If we filter "Today", "Present Asset" = "Stock Added Today" - "Lost Today" - "Damaged Today".
        // This represents the *change* in present assets for that period, not the absolute current stock.
        // However, this is consistent with "Income Today" or "Expense Today".
        $presentAsset = $totalAsset - $lostAsset - $damageAsset;

        return Inertia::render('asset/Dashboard', [
            'transactions' => $transactions,
            'stocks'        => $stocks,
            'issues'        => $issues,
            'lostItems'     => $lostItems,
            'stats'         => [
                'total_asset'   => $totalAsset,
                'present_asset' => $presentAsset,
                'lost_asset'    => $lostAsset,
                'damage_asset'  => $damageAsset,
            ],
            'filters' => [
                'filter_type' => $filterType,
                'date' => $date,
                'month' => $month,
            ]
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
        dd($request->all());
        DB::beginTransaction();
        try {
            $stock = NewAssetStock::findOrFail($id);

            // Update এ সব ফিল্ড বাধ্যতামূলক না, শুধু যা আসবে তাই validate হবে
            $data = $request->validate([
                'product_name'         => 'nullable|string|max:255',
                'product_category'     => 'nullable|string|max:255',
                'vendor'               => 'nullable|string|max:255',
                'quantity'             => 'nullable|numeric|min:1',
                'unit_price'           => 'nullable|numeric|min:0',
                'total_price'          => 'nullable|numeric',
                'location'             => 'nullable|string|max:255',
                'has_warranty'         => 'sometimes|boolean',
                'warranty_expiry_date' => 'nullable|date|after:today',
                'photo'                => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);
            $imagePath = $stock->photo;
            if ($request->hasFile('photo')) {
                $imagePath = $request->file('photo')
                    ->store('assets/products', 'public');
            }

            if ($request->has('has_warranty') && $request->has_warranty) {
                $data['warranty_expiry_date'] = Carbon::parse($request->warranty_expiry_date)->format('Y-m-d');
            }

            // পুরানো ভ্যালু ধরে রেখে কেবল পাঠানো ফিল্ড আপডেট করব
            $merged = [
                'product_name'         => $stock->product_name,
                'product_category'     => $stock->product_category,
                'vendor'               => $stock->vendor,
                'quantity'             => $stock->quantity,
                'unit_price'           => $stock->unit_price,
                'total_price'          => $stock->total_price,
                'location'             => $stock->location,
                'has_warranty'         => $stock->has_warranty,
                'warranty_expiry_date' => $stock->warranty_expiry_date,
                'photo'                => $imagePath,
            ];

            $merged = array_merge($merged, $data);

            $qty  = $merged['quantity'] ?? $stock->quantity;
            $unit = $merged['unit_price'] ?? $stock->unit_price;
            $merged['total_price'] = round($qty * $unit);

            $stock->update($merged);

            DB::commit();
            return redirect()->back()->with('success', 'স্টক সফলভাবে আপডেট করা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Stock Update Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine(),
            ]);
            return redirect()->back()->with('error', 'স্টক আপডেট সমস্যা আছে।');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $stock = NewAssetStock::findOrFail($id);

            $hasIssues = AssetIssue::where('stock_id', $stock->id)->exists();
            if ($hasIssues) {
                return redirect()->back()->with('error', 'ইস্যু করা স্টক ডিলিট করা যাবে না।');
            }

            $stock->delete();

            DB::commit();
            return redirect()->back()->with('success', 'স্টক সফলভাবে ডিলিট করা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Stock Delete Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine(),
            ]);
            return redirect()->back()->with('error', 'স্টক ডিলিট সমস্যা আছে।');
        }
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

    public function issueLost(Request $request, AssetIssue $issue)
    {
        //dd($issue, $request->all());
        $data = $request->validate([
            'quantity'  => ['required', 'numeric', 'min:1'],
            'lost_date' => ['required', 'date'],
            'reported'  => ['required', 'string'],
        ]);
        
        DB::beginTransaction();
        try {
            $lostQty = AssetLost::where('issue_id', $issue->id)->sum('quantity');
            $damageQty = IdelAsset::where('issue_id', $issue->id)->sum('quantity');
            $available = $issue->quantity - ($lostQty + $damageQty);

            if ($data['quantity'] > $available) {
                return redirect()->back()->with('error', 'প্রোডাক্টের পরিমাণ পর্যাপ্ত নয়।');
            }

            AssetLost::create([
                'issue_id'         => $issue->id,
                'product_name'     => $issue->product_name,
                'product_category' => $issue->product_category,
                'reported'         => $data['reported'] ?? null,
                'lost_date'        => $data['lost_date'],
                'quantity'         => $data['quantity'],
                'season'           => Auth::user()->season,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'অ্যাসেট লস্ট হিসেবে সংরক্ষণ করা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Asset Lost Create Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine(),
            ]);
            return redirect()->back()->with('error', 'অ্যাসেট লস্ট প্রক্রিয়ায় সমস্যা হয়েছে।');
        }
    }

    public function issueDamage(Request $request, AssetIssue $issue)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'quantity'  => ['required', 'numeric', 'min:1'],
                'lost_date' => ['required', 'date'],
                'reported'  => ['nullable', 'string'],
            ]);

            $lostQty = AssetLost::where('issue_id', $issue->id)->sum('quantity');
            $damageQty = IdelAsset::where('issue_id', $issue->id)->sum('quantity');
            $available = $issue->quantity - ($lostQty + $damageQty);

            if ($data['quantity'] > $available) {
                return redirect()->back()->with('error', 'প্রোডাক্টের পরিমাণ পর্যাপ্ত নয়।');
            }

            IdelAsset::create([
                'issue_id'         => $issue->id,
                'product_name'     => $issue->product_name,
                'product_category' => $issue->product_category,
                'reported'         => $data['reported'] ?? null,
                'lost_date'        => $data['lost_date'],
                'quantity'         => $data['quantity'],
                'season'           => Auth::user()->season,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'অ্যাসেট ড্যামেজ হিসেবে সংরক্ষণ করা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Asset Damage Create Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine(),
            ]);
            return redirect()->back()->with('error', 'অ্যাসেট ড্যামেজ প্রক্রিয়ায় সমস্যা হয়েছে।');
        }
    }

    public function issueUpdate(IssueRequest $request, AssetIssue $issue)
    {
        try {
            $issue->update([
                'to_whom'   => $request->name,
                'issue_date'=> $request->issue_date,
                'location'  => $request->location,
                'quantity'  => $request->quantity,
            ]);

            return redirect()->back()->with('success', 'ইস্যু সফলভাবে আপডেট হয়েছে!');
        } catch (\Throwable $th) {
            Log::error('Issue Update Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine(),
            ]);
            return redirect()->back()->with('error', 'ইস্যু আপডেট সমস্যা আছে।');
        }
    }

    public function issueDestroy(AssetIssue $issue)
    {
        try {
            $issue->delete();
            return redirect()->back()->with('success', 'ইস্যু সফলভাবে ডিলিট হয়েছে!');
        } catch (\Throwable $th) {
            Log::error('Issue Delete Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine(),
            ]);
            return redirect()->back()->with('error', 'ইস্যু ডিলিট সমস্যা আছে।');
        }
    }

    public function lostUpdate(Request $request, AssetLost $lost)
    {
        $data = $request->validate([
            'quantity'  => ['required', 'numeric', 'min:1'],
            'lost_date' => ['required', 'date'],
            'reported'  => ['required', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $issue = $lost->issue;

            if (!$issue) {
                throw new \Exception('Issue not found for this lost item.');
            }
            
            // Calculate max allowed
            $otherLostQty = AssetLost::where('issue_id', $issue->id)->where('id', '!=', $lost->id)->sum('quantity');
            $damageQty = IdelAsset::where('issue_id', $issue->id)->sum('quantity');
            $available = $issue->quantity - ($otherLostQty + $damageQty);

            if ($data['quantity'] > $available) {
                 return redirect()->back()->with('error', 'প্রোডাক্টের পরিমাণ পর্যাপ্ত নয়।');
            }

            $lost->update([
                'reported'  => $data['reported'],
                'lost_date' => $data['lost_date'],
                'quantity'  => $data['quantity'],
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'অ্যাসেট লস্ট আপডেট করা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Asset Lost Update Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine(),
            ]);
            return redirect()->back()->with('error', 'অ্যাসেট লস্ট আপডেটে সমস্যা হয়েছে।');
        }
    }

    public function lostDestroy(AssetLost $lost)
    {
        try {
            $lost->delete();
            return redirect()->back()->with('success', 'অ্যাসেট লস্ট ডিলিট করা হয়েছে!');
        } catch (\Throwable $th) {
             Log::error('Asset Lost Delete Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine(),
            ]);
            return redirect()->back()->with('error', 'অ্যাসেট লস্ট ডিলিটে সমস্যা হয়েছে।');
        }
    }
}

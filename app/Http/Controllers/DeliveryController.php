<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryRequest;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Active items list
        $items = Item::active()->get();

        // Auto-generate next invoice number
        $currentYear = Carbon::now()->format('y');
        $lastId = DB::table('invoices')->max('id');
        $lastIddel = DB::table('deliveries')->max('id');
        $deliveryNumber = $currentYear . str_pad($lastIddel + 1, 4, '0', STR_PAD_LEFT);

        // Base query with relations
        $query = Invoice::with([
            'customer:id,name,phone,address',
            'invoiceDetails.item:id,name',
            'creator:id,name',
        ])->orderBy('id', 'desc');

        // Search & Date filters
        $search = $request->input('search');
        $date = $request->input('date');

        // 🔹 Date filter logic
        if ($date) {
            // যদি ইউজার তারিখ দেয়, সেই তারিখ অনুযায়ী ফিল্টার করবে
            $query->whereDate('delivery_date', Carbon::parse($date)->format('Y-m-d'));
        } else {
            // নাহলে আজকের ডেটা দেখাবে
            $query->whereDate('delivery_date', Carbon::now()->format('Y-m-d'));
        }

        // 🔹 Search filter logic
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    });
            });
        }

        // Pagination
        $perPage = $request->perPage ?? 10;

        // Render with Inertia
        return Inertia::render('delivery/TodayDelivery', [
            'invoices' => $query->paginate($perPage)->withQueryString(),
            'items' => $items,
            'deliveryNumber' => $deliveryNumber,
            'filters' => [
                'search' => $search,
                'date' => $date,
                'perPage' => $perPage,
            ],
        ]);
    }

    public function alldelivery(Request $request)
    {
        // Active items list
        $items = Item::active()->get();

        // Auto-generate next delivery number
        $currentYear = Carbon::now()->format('y');
        $lastIddel = DB::table('deliveries')->max('id');
        $deliveryNumber = $currentYear . str_pad($lastIddel + 1, 4, '0', STR_PAD_LEFT);

        // Base query with relations
        $query = Delivery::with([
            'customer:id,name,phone,address,due_amount',
            'item:id,name',
            'creator:id,name',
            'invoice:id,invoice_no,quantity',
        ])->orderBy('id', 'desc');

        // Filter by session (season)
        if (Auth::user()->season) {
             $query->where('season', Auth::user()->season);
        }

        // Search filter
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('delivery_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoice', function ($q3) use ($search) {
                        $q3->where('invoice_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('item', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Date range filter
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        
        if ($start_date && $end_date) {
             $query->whereBetween('delivery_date', [$start_date, $end_date]);
        }

        // Pagination
        $perPage = $request->perPage ?? 10;

        // Render with Inertia
        return Inertia::render('delivery/AllDelivery', [
            'deliveries' => $query->paginate($perPage)->withQueryString(),
            'items' => $items,
            'deliveryNumber' => $deliveryNumber,
            'filters' => [
                'search' => $search,
                'start_date' => $start_date,
                'end_date' => $end_date,
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
    public function store(DeliveryRequest $request)
    {
        DB::beginTransaction();

        try {
            // 🔹 Invoice Details
            $invoice = Invoice::where('invoice_no', $request->invoice_no)->first();
            if (!$invoice) {
                throw new \Exception('চালান সংরক্ষণে একটি ত্রুটি ঘটেছে!');
            }

            if (isset($request->next_delivery_date) && !empty($request->next_delivery_date)) {
                $invoice->update([
                    'delivery_date' => $request->next_delivery_date,
                ]);
            }

            $details = InvoiceDetails::where('invoice_id', $invoice->id)->where('item_id', $request->item_id)->first();
            if (!$details) {
                throw new \Exception('চালান সংরক্ষণে একটি ত্রুটি ঘটেছে!');
            }

            $details->update([
                'delivery_quantity' => $details->delivery_qty + $request->today_delivery_qty,
            ]);


            $delivery = new Delivery();
            $delivery->fill([
                'season' => Auth::user()->season ?? date('Y'),
                'delivery_no' => $request->delivery_no,
                'delivery_date' => $request->delivery_date,
                'invoice_no' => $request->invoice_no,
                'customer_id' => $request->customer_id,
                'item_id' => $request->item_id,
                'address' => $request->address,
                'driver_name' => $request->driver_name,
                'driver_phone' => $request->driver_phone,
                'truck_number' => $request->truck_number,
                'quantity' => $request->delivery_qty,
                'rate' => $details->rate,
                'amount' => $request->today_delivery_qty * $details->rate,
                'delivery_qty' => $request->today_delivery_qty,
                'delivery_rant' => $request->delivery_rant,
                'note' => $request->note,
                'is_active' => 1
            ]);
            $delivery->save();

            DB::commit();
            return redirect()->back()->with('success', 'চালান সংরক্ষণে সফলভাবে সংরক্ষণ হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Invoice Delivery Error: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'চালান সংরক্ষণে একটি ত্রুটি ঘটেছে!');
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
        DB::beginTransaction();

        try {
            $delivery = Delivery::findOrFail($id);
            $invoice = Invoice::where('invoice_no', $delivery->invoice_no)->first();
            
            if (!$invoice) {
                throw new \Exception('চালান পাওয়া যায়নি!');
            }

            // Find Invoice Details
            $details = InvoiceDetails::where('invoice_id', $invoice->id)
                                    ->where('item_id', $delivery->item_id)
                                    ->first();

            if (!$details) {
                throw new \Exception('চালানের বিস্তারিত তথ্য পাওয়া যায়নি!');
            }

            // Calculate quantity logic
            // alreadyDeliveredByOthers = Current Total Delivered - This Delivery's Previous Amount
            $alreadyDeliveredByOthers = max(0, $details->delivery_quantity - $delivery->delivery_qty);
            $maxPossibleNewDelivery = $details->quantity - $alreadyDeliveredByOthers;

            if ($request->today_delivery_qty > $maxPossibleNewDelivery) {
                throw new \Exception("সর্বোচ্চ ডেলিভারি পরিমাণ {$maxPossibleNewDelivery} হতে পারে!");
            }

            // Update Invoice Details
            $newTotalDelivered = $alreadyDeliveredByOthers + $request->today_delivery_qty;
            
            $details->update([
                'delivery_quantity' => $newTotalDelivered,
            ]);

            // Update Delivery Record
            $delivery->update([
                'delivery_date' => $request->delivery_date,
                'address' => $request->address,
                'driver_name' => $request->driver_name,
                'driver_phone' => $request->driver_phone,
                'truck_number' => $request->truck_number,
                'quantity' => $maxPossibleNewDelivery, // Update available snapshot
                'delivery_qty' => $request->today_delivery_qty,
                'amount' => $request->today_delivery_qty * $details->rate,
                'delivery_rant' => $request->delivery_rant,
                'note' => $request->note,
            ]);

            if ($request->customer_id) {
                $delivery->update(['customer_id' => $request->customer_id]);
            }
            
            if (isset($request->next_delivery_date) && !empty($request->next_delivery_date)) {
                $invoice->update([
                    'delivery_date' => $request->next_delivery_date,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'ডেলিভারি আপডেট সফল হয়েছে!');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Delivery Update Error: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $delivery = Delivery::findOrFail($id);

            // 🔹 Invoice Details revert
            $details = InvoiceDetails::where('invoice_id', $delivery->invoice->id)
                ->where('item_id', $delivery->item_id)
                ->first();

            if ($details) {
                $details->update([
                    'delivery_quantity' => max(0, $details->delivery_quantity - $delivery->delivery_qty),
                ]);
            }

            // 🔹 Invoice delivery_date restore
            $lastDelivery = Delivery::where('invoice_no', $delivery->invoice_no)
                ->where('id', '!=', $delivery->id)
                ->orderBy('delivery_date', 'desc')
                ->first();

            if ($lastDelivery) {
                // আগের সর্বশেষ delivery date restore
                $delivery->invoice->update([
                    'delivery_date' => $lastDelivery->delivery_date,
                ]);
            } else {
                // যদি আর কোন delivery না থাকে → fallback date (invoice creation date)
                $fallbackDate = $delivery->invoice->created_at->format('Y-m-d');

                $delivery->invoice->update([
                    'delivery_date' => $fallbackDate,
                ]);
            }

            // 🔹 Delete delivery row
            $delivery->delete();

            DB::commit();

            return back()->with('success', 'ডেলিভারি সফলভাবে ডিলিট করা হয়েছে!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Delivery Delete Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'ডেলিভারি ডিলিট করতে সমস্যা হয়েছে!');
        }
    }


    public function invoice(Request $request)
    {
        $search = $request->query('q', '');
        $invoice = Invoice::with([
            'customer:id,name,phone,address,due_amount',
            'creator:id,name',
            'invoiceDetails:id,invoice_id,item_id,quantity,rate,amount,delivery_quantity',
            'invoiceDetails.item:id,name'
        ])->where('invoice_no', 'like', "%{$search}%")->first();

        return response()->json($invoice);
    }
}

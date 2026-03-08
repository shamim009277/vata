<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryRequest;
use App\Http\Requests\InvoiceRequest;
use App\Models\BusinessStore;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Item;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InvoiceController extends Controller
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
        $invoiceNumber = $currentYear . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        $deliveryNumber = $currentYear . str_pad($lastIddel + 1, 4, '0', STR_PAD_LEFT);

        // Base query with relations
        $query = Invoice::with([
            'customer:id,name,phone,address',
            'creator:id,name',
            'invoiceDetails:id,invoice_id,item_id,quantity,rate,amount,delivery_quantity',
            'invoiceDetails.item:id,name'
        ])->orderBy('id', 'desc');

        // Search & Date filters
        $search = $request->input('search');
        $date = $request->input('date');

        // 🔹 Date filter logic
        if ($date) {
            // যদি ইউজার তারিখ দেয়, সেই তারিখ অনুযায়ী ফিল্টার করবে
            $query->whereDate('invoice_date', Carbon::parse($date)->format('Y-m-d'));
        } else {
            // নাহলে আজকের ডেটা দেখাবে
            $query->whereDate('invoice_date', Carbon::now()->format('Y-m-d'));
        }

        // 🔹 Search filter logic
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoiceDetails.item', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Pagination
        $perPage = $request->perPage ?? 10;

        // Render with Inertia
        return Inertia::render('invoices/TodayInvoice', [
            'invoices' => $query->paginate($perPage)->withQueryString(),
            'items' => $items,
            'invoiceNumber' => $invoiceNumber,
            'deliveryNumber' => $deliveryNumber,
            'business_store' => BusinessStore::first(),
            'filters' => [
                'search' => $search,
                'date' => $date,
                'perPage' => $perPage,
            ],
        ]);
    }



    public function advanceinvoice(Request $request){
        //Active items list
        $items = Item::active()->get();

        //Auto-generate next invoice number
        $currentYear = Carbon::now()->format('y');
        $lastId = DB::table('invoices')->max('id');
        $lastIddel = DB::table('deliveries')->max('id');
        $nextId = $lastId + 1;
        $nextIdDel = $lastIddel + 1;
        $invoiceNumber = $currentYear . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $deliveryNumber = $currentYear . str_pad($nextIdDel, 4, '0', STR_PAD_LEFT);

        //Base query with relations
        $query = Invoice::with([
            'customer:id,name,phone,address',
            'creator:id,name',
            'invoiceDetails:id,invoice_id,item_id,quantity,rate,amount,delivery_quantity',
            'invoiceDetails.item:id,name'
        ])->where('invoice_type', 'অগ্রিম')->orderBy('id', 'desc');

        //Search filter
        $search = $request->input('search');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoiceDetails.item', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($start_date && $end_date) {
            $start = Carbon::parse($start_date)->startOfDay();
            $end = Carbon::parse($end_date)->endOfDay();
            $query->whereBetween('invoice_date', [$start, $end]);
        }

        //Pagination
        $perPage = $request->perPage ?? 10;

        //Render with Inertia
        return Inertia::render('invoices/AdvanceInvoice', [
            'invoices' => $query->paginate($perPage)->withQueryString(),
            'items' => $items,
            'invoiceNumber' => $invoiceNumber,
            'deliveryNumber' => $deliveryNumber,
            'business_store' => BusinessStore::first(),
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
        ]);
    }

    public function allinvoice(Request $request){
        //Active items list
        $items = Item::active()->get();

        //Auto-generate next invoice number
        $currentYear = Carbon::now()->format('y');
        $lastId = DB::table('invoices')->max('id');
        $lastIddel = DB::table('deliveries')->max('id');
        $nextId = $lastId + 1;
        $nextIdDel = $lastIddel + 1;
        $invoiceNumber = $currentYear . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $deliveryNumber = $currentYear . str_pad($nextIdDel, 4, '0', STR_PAD_LEFT);

        //Base query with relations
        $query = Invoice::with([
            'customer:id,name,phone,address',
            'creator:id,name',
            'invoiceDetails:id,invoice_id,item_id,quantity,rate,amount,delivery_quantity',
            'invoiceDetails.item:id,name'
        ])->orderBy('id', 'desc');

        //Search filter
        $search = $request->input('search');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoiceDetails.item', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($start_date && $end_date) {
            $start = Carbon::parse($start_date)->startOfDay();
            $end = Carbon::parse($end_date)->endOfDay();
            $query->whereBetween('invoice_date', [$start, $end]);
        }

        //Pagination
        $perPage = $request->perPage ?? 10;

        //Render with Inertia
        return Inertia::render('invoices/AllInvoice', [
            'invoices' => $query->paginate($perPage)->withQueryString(),
            'items' => $items,
            'invoiceNumber' => $invoiceNumber,
            'deliveryNumber' => $deliveryNumber,
            'business_store' => BusinessStore::first(),
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
    // public function store(InvoiceRequest $request)
    // {
    //     // quantity মোট যোগফল বের করা
    //     $totalQuantity = collect($request->items)->sum('quantity');

    //     DB::beginTransaction(); 

    //     try {
    //         $customer = Customer::updateOrCreate(
    //             [
    //                 'phone' => $request->phone,
    //             ],
    //             [
    //                 'name'               => $request->customer_name,
    //                 'address'            => $request->address,
    //                 'season'             => 2425,
    //                 'next_delivery_date' => $request->delivery_date,
    //             ]
    //         );

    //         $invoice = new Invoice();
    //         $invoice->invoice_no       = $request->invoice_no;
    //         $invoice->invoice_date     = $request->invoice_date;
    //         $invoice->customer_id      = $customer->id;
    //         $invoice->season           = 2425;
    //         $invoice->invoice_type     = $request->type;
    //         $invoice->delivery_date    = $request->delivery_date;
    //         $invoice->note             = $request->note ?? null;
    //         $invoice->quantity         = $totalQuantity;
    //         $invoice->total_amount     = $request->total_amount;
    //         $invoice->sub_total        = $request->sub_total;
    //         $invoice->paid_amount      = $request->paid_amount;
    //         $invoice->due_amount       = $request->due_amount;
    //         $invoice->discount         = $request->discount ?? 0;
    //         $invoice->rant             = $request->rant ?? 0;
    //         $invoice->next_payment_date = $request->payment_date ?? null;
    //         $invoice->save();

    //         $invoiceDetails = collect($request->items)->map(function ($item) use ($invoice) {
    //             return [
    //                 'invoice_id' => $invoice->id,
    //                 'item_id'    => $item['item_id'] ?? null,
    //                 'quantity'   => $item['quantity'],
    //                 'rate'       => $item['rate'],
    //                 'amount'     => $item['amount'],
    //                 'created_by' => Auth::user()->id,
    //                 'updated_by' => Auth::user()->id,
    //             ];
    //         })->toArray();

    //         InvoiceDetails::insert($invoiceDetails);

    //         $customer->update([
    //             'total_item'         => DB::raw('total_item + ' . $totalQuantity),
    //             'total_amount'       => DB::raw('total_amount + ' . $request->total_amount),
    //             'paid_amount'        => DB::raw('paid_amount + ' . $request->paid_amount),
    //             'due_amount'         => DB::raw('due_amount + ' . $request->due_amount),
    //             'next_delivery_date' => $request->delivery_date,
    //             'next_payment_date'  => $request->payment_date,
    //         ]);

    //         DB::commit();

    //         return redirect()->route('invoices.index')->with('success', 'চালান সফলভাবে তৈরি হয়েছে!');
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         dd($e->getMessage(), $e->getTraceAsString());
    //         Log::error('Invoice Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

    //         return redirect()->back()->with('error', 'চালান তৈরির সময় একটি ত্রুটি ঘটেছে!');
    //     }
    // }

    public function store(InvoiceRequest $request)
    {
        DB::beginTransaction();

        try {
            $totalQuantity = collect($request->items)->sum('quantity');

            // 🔹 Customer create or update
            $customer = Customer::updateOrCreate(
                ['phone' => $request->phone],
                [
                    'name'               => $request->customer_name,
                    'address'            => $request->address,
                    'season'             => 2425,
                    'next_delivery_date' => $request->delivery_date,
                ]
            );

            $isUpdate = $request->id ? true : false;
            $oldTotals = [
                'quantity' => 0,
                'total'    => 0,
                'paid'     => 0,
                'due'      => 0,
            ];

            // 🔹 যদি update হয়, পুরনো invoice ডেটা রেখে দাও
            if ($isUpdate) {
                $invoice = Invoice::with('invoiceDetails')->find($request->id);

                if (!$invoice) {
                    // যদি ID দেওয়া থাকে কিন্তু invoice না থাকে → create করো
                    $invoice = new Invoice();
                } else {
                    $oldTotals = [
                        'quantity' => $invoice->invoiceDetails->sum('quantity'),
                        'total'    => $invoice->total_amount,
                        'paid'     => $invoice->paid_amount,
                        'due'      => $invoice->due_amount,
                    ];
                }
            } else {
                $invoice = new Invoice();
            }

            // 🔹 Invoice save or update
            $invoice->fill([
                'invoice_no'        => $request->invoice_no,
                'invoice_date'      => $request->invoice_date,
                'customer_id'       => $customer->id,
                'season'            => 2425,
                'invoice_type'      => $request->type,
                'delivery_date'     => $request->delivery_date,
                'note'              => $request->note ?? null,
                'quantity'          => $totalQuantity,
                'total_amount'      => $request->total_amount,
                'sub_total'         => $request->sub_total,
                'paid_amount'       => $request->paid_amount,
                'due_amount'        => $request->due_amount,
                'discount'          => $request->discount ?? 0,
                'rant'              => $request->rant ?? 0,
                'next_payment_date' => $request->payment_date ?? null,
            ]);
            $invoice->save();

            // 🔹 পুরনো item IDs
            $existingItemIds = $invoice->invoiceDetails()->pluck('item_id')->toArray();

            // 🔹 নতুন item IDs
            $newItemIds = collect($request->items)->pluck('item_id')->toArray();

            // 🔹 যেগুলো remove হয়ে গেছে সেগুলো delete করো
            $itemsToDelete = array_diff($existingItemIds, $newItemIds);
            if (!empty($itemsToDelete)) {
                InvoiceDetails::where('invoice_id', $invoice->id)
                    ->whereIn('item_id', $itemsToDelete)
                    ->delete();
            }

            // 🔹 Items insert or update
            foreach ($request->items as $item) {
                InvoiceDetails::updateOrCreate(
                    [
                        'invoice_id' => $invoice->id,
                        'item_id'    => $item['item_id'],
                    ],
                    [
                        'quantity'   => $item['quantity'],
                        'rate'       => $item['rate'],
                        'amount'     => $item['amount'],
                        'updated_by' => Auth::id(),
                        'created_by' => Auth::id(),
                    ]
                );
            }

            // 🔹 Customer totals update (update হলে পুরনো বাদ দিয়ে নতুন যোগ)
            if ($isUpdate) {
                $customer->update([
                    'total_item'   => DB::raw('total_item - ' . $oldTotals['quantity'] . ' + ' . $totalQuantity),
                    'total_amount' => DB::raw('total_amount - ' . $oldTotals['total'] . ' + ' . $request->total_amount),
                    'paid_amount'  => DB::raw('paid_amount - ' . $oldTotals['paid'] . ' + ' . $request->paid_amount),
                    'due_amount'   => DB::raw('due_amount - ' . $oldTotals['due'] . ' + ' . $request->due_amount),
                    'next_delivery_date' => $request->delivery_date,
                    'next_payment_date'  => $request->payment_date,
                ]);
            } else {
                $customer->update([
                    'total_item'         => DB::raw('total_item + ' . $totalQuantity),
                    'total_amount'       => DB::raw('total_amount + ' . $request->total_amount),
                    'paid_amount'        => DB::raw('paid_amount + ' . $request->paid_amount),
                    'due_amount'         => DB::raw('due_amount + ' . $request->due_amount),
                    'next_delivery_date' => $request->delivery_date,
                    'next_payment_date'  => $request->payment_date,
                ]);
            }

            DB::commit();

            $message = $isUpdate
                ? 'চালান সফলভাবে আপডেট হয়েছে!'
                : 'চালান সফলভাবে তৈরি হয়েছে!';

            return redirect()->route('invoices.index')->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Invoice Store/Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $exist = Delivery::where('invoice_no', $invoice->invoice_no)->exists();
            if ($exist) {
                return redirect()->back()->with('error', 'চালান ডিলিট সমস্যা আছে: ডেলিভারি আছে।');
            }else{
                InvoiceDetails::where('invoice_id', $id)->delete();
                Invoice::findOrFail($id)->delete();
                return redirect()->back()->with('success', 'চালান সফলভাবে ডিলিট করা হয়েছে');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'চালান ডিলিট সমস্যা আছে');
        }
    }

    public function invoiceDelivary(DeliveryRequest $request){
        DB::beginTransaction();

        try {
            // 🔹 Invoice Details
            $invoice = Invoice::where('invoice_no', $request->invoice_no)->first();
            if (!$invoice) {
                throw new \Exception('চালান সংরক্ষণে একটি ত্রুটি ঘটেছে!');
            }

            if(isset($request->next_delivery_date) && !empty($request->next_delivery_date)){
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
                'season' => 2025,
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

            // Send SMS if requested
            if ($request->send_sms) {
                try {
                    $smsService = new SmsService();
                    $message = "প্রিয় গ্রাহক, আপনার ডেলিভারি সম্পন্ন হয়েছে। ডেলিভারি নং: {$delivery->delivery_no}, পরিমাণ: {$delivery->delivery_qty}, গাড়ী নং: {$delivery->truck_number}। ধন্যবাদ।";
                    $smsService->send($request->phone, $message);
                } catch (\Exception $e) {
                    Log::error('SMS Sending Error: ' . $e->getMessage());
                    // Don't fail the transaction if SMS fails, just log it
                }
            }

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
}

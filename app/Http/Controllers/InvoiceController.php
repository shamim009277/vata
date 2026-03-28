<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryRequest;
use App\Http\Requests\InvoiceRequest;
use App\Models\BusinessStore;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\InvoicePayment;
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
            'customer:id,name,phone,address,due_amount',
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
            'customer:id,name,phone,address,due_amount',
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
            'customer:id,name,phone,address,due_amount',
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

    public function regularInvoice(Request $request){
        $items = Item::active()->get();
        $currentYear = Carbon::now()->format('y');
        $lastId = DB::table('invoices')->max('id');
        $lastIddel = DB::table('deliveries')->max('id');
        $nextId = $lastId + 1;
        $nextIdDel = $lastIddel + 1;
        $invoiceNumber = $currentYear . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $deliveryNumber = $currentYear . str_pad($nextIdDel, 4, '0', STR_PAD_LEFT);

        $query = Invoice::with([
            'customer:id,name,phone,address,due_amount',
            'creator:id,name',
            'invoiceDetails:id,invoice_id,item_id,quantity,rate,amount,delivery_quantity',
            'invoiceDetails.item:id,name'
        ])->where('invoice_type', 'রেগুলার')->orderBy('id', 'desc');

        $search = $request->input('search');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $season = $request->input('season', session('season'));

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

        if ($season) {
            $query->where('season', $season);
        }

        $perPage = $request->perPage ?? 10;

        return Inertia::render('invoices/AllInvoice', [
            'invoices' => $query->paginate($perPage)->withQueryString(),
            'items' => $items,
            'invoiceNumber' => $invoiceNumber,
            'deliveryNumber' => $deliveryNumber,
            'business_store' => BusinessStore::first(),
            'filters' => [
                'search' => $search,
                'season' => $season,
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

            // 🔹 Upsert payment record for this invoice
            InvoicePayment::updateOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'customer_id'  => $customer->id,
                    'payment_date' => $request->payment_date,
                    'total_amount' => $request->total_amount,
                    'paid_amount'  => $request->paid_amount,
                    'due_amount'   => $request->due_amount,
                    'method'       => $request->payment_method ?: 'cash',
                    'account_number' => $request->payment_method === 'mobile_banking' ? ($request->account_number ?? null) : null,
                    'check_number'   => $request->payment_method === 'check' ? ($request->check_number ?? null) : null,
                    'note'         => $request->note ?? null,
                    'created_by'   => Auth::id(),
                    'updated_by'   => Auth::id(),
                ]
            );

            DB::commit();

            // 🔹 Send SMS on create/update if requested
            if ($request->boolean('send_sms')) {
                try {
                    $smsService = new SmsService();
                    $msg = "প্রিয় গ্রাহক, আপনার চালান তৈরি হয়েছে। চালান নং: {$invoice->invoice_no}, মোট: {$invoice->total_amount}, নগদ: {$invoice->paid_amount}, বাকি: {$invoice->due_amount}। ধন্যবাদ।";
                    $smsService->send($request->phone, $msg);
                } catch (\Exception $e) {
                    Log::error('Invoice SMS Error: ' . $e->getMessage());
                }
            }

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

    // --- Due Khata Sections ---
    public function todayDeposit(Request $request)
    {
        $query = InvoicePayment::with([
            'customer:id,name,phone,address',
            'invoice:id,invoice_no,total_amount,paid_amount,due_amount'
        ])
        ->whereDate('payment_date', Carbon::now()->format('Y-m-d'))
        ->orderBy('id', 'desc');

        // Search logic
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhereHas('invoice', function ($q3) use ($search) {
                    $q3->where('invoice_no', 'like', "%{$search}%");
                });
            });
        }

        $perPage = $request->perPage ?? 10;
        return Inertia::render('due/TodayDeposit', [
            'payments' => $query->paginate($perPage)->withQueryString(),
            'business_store' => BusinessStore::first(),
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
        ]);
    }

    public function willDepositToday(Request $request)
    {
        $items = Item::active()->get();
        $currentYear = Carbon::now()->format('y');
        $lastId = DB::table('invoices')->max('id');
        $lastIddel = DB::table('deliveries')->max('id');
        $invoiceNumber = $currentYear . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        $deliveryNumber = $currentYear . str_pad($lastIddel + 1, 4, '0', STR_PAD_LEFT);

        $query = Invoice::with([
            'customer:id,name,phone,address,due_amount',
            'creator:id,name',
            'invoiceDetails:id,invoice_id,item_id,quantity,rate,amount,delivery_quantity',
            'invoiceDetails.item:id,name'
        ])
        ->where('due_amount', '>', 0)
        ->whereDate('next_payment_date', Carbon::now()->format('Y-m-d'))
        ->orderBy('id', 'desc');

        $perPage = $request->perPage ?? 10;
        return Inertia::render('due/TodayDue', [
            'invoices' => $query->paginate($perPage)->withQueryString(),
            'items' => $items,
            'invoiceNumber' => $invoiceNumber,
            'deliveryNumber' => $deliveryNumber,
            'business_store' => BusinessStore::first(),
            'filters' => [
                'perPage' => $perPage,
            ],
        ]);
    }

    public function allDue(Request $request)
    {
        $items = Item::active()->get();
        $currentYear = Carbon::now()->format('y');
        $lastId = DB::table('invoices')->max('id');
        $lastIddel = DB::table('deliveries')->max('id');
        $invoiceNumber = $currentYear . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        $deliveryNumber = $currentYear . str_pad($lastIddel + 1, 4, '0', STR_PAD_LEFT);

        $query = Invoice::with([
            'customer:id,name,phone,address,due_amount',
            'creator:id,name',
            'invoiceDetails:id,invoice_id,item_id,quantity,rate,amount,delivery_quantity',
            'invoiceDetails.item:id,name'
        ])
        ->where('due_amount', '>', 0)
        ->orderBy('id', 'desc');

        // Optional filters
        $search = $request->input('search');
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

        $perPage = $request->perPage ?? 10;
        return Inertia::render('due/AllDue', [
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

    public function invoicePayments(Invoice $invoice)
    {
        $invoice->load(['customer', 'payments']);
        return Inertia::render('invoices/InvoicePayments', [
            'invoice' => $invoice,
            'payments' => $invoice->payments,
            'business_store' => BusinessStore::first(),
        ]);
    }

    public function invoicePaymentsJson(Invoice $invoice)
    {
        $invoice->load('payments');
        return response()->json($invoice->payments);
    }

    public function updatePayment(Request $request, InvoicePayment $payment)
    {
        $invoice = Invoice::find($payment->invoice_id);
        $validated = $request->validate([
            'payment_date' => 'nullable|date',
            'paid_amount'  => ['nullable', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($payment, $invoice) {
                if ($invoice) {
                    $otherPaymentsSum = InvoicePayment::where('invoice_id', $invoice->id)
                        ->where('id', '!=', $payment->id)
                        ->sum('paid_amount');
                    if (($otherPaymentsSum + $value) > $invoice->total_amount) {
                        $fail('পরিশোধিত পরিমাণ মোট মূল্যের চেয়ে বেশি হতে পারে না।');
                    }
                }
            }],
            'payment_method' => 'nullable|string|in:cash,mobile_banking,check',
            'next_payment_date' => 'nullable|date|after_or_equal:payment_date',
            'account_number' => 'nullable|required_if:payment_method,mobile_banking|string|max:50',
            'check_number'   => 'nullable|required_if:payment_method,check|string|max:50',
            'note'           => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $oldPaidAmount = $payment->paid_amount;
            $newPaidAmount = $validated['paid_amount'] ?? $oldPaidAmount;
            $diff = $newPaidAmount - $oldPaidAmount;

            // Update Invoice
            if ($invoice) {
                $invoice->paid_amount += $diff;
                $invoice->due_amount -= $diff;
                if (isset($validated['next_payment_date'])) {
                    $invoice->next_payment_date = $validated['next_payment_date'];
                }
                $invoice->save();
            }

            // Update Customer
            $customer = Customer::find($payment->customer_id);
            if ($customer) {
                $customer->paid_amount += $diff;
                $customer->due_amount -= $diff;
                if (isset($validated['next_payment_date'])) {
                    $customer->next_payment_date = $validated['next_payment_date'];
                }
                $customer->save();
            }

            // Update Payment record
            $payment->update([
                'payment_date' => $validated['payment_date'] ?? $payment->payment_date,
                'paid_amount'  => $newPaidAmount,
                'due_amount'   => $invoice ? $invoice->due_amount : $payment->due_amount,
                'method'       => $validated['payment_method'] ?? $payment->method,
                'account_number' => ($validated['payment_method'] ?? $payment->method) === 'mobile_banking' ? ($validated['account_number'] ?? null) : null,
                'check_number'   => ($validated['payment_method'] ?? $payment->method) === 'check' ? ($validated['check_number'] ?? null) : null,
                'note'         => $validated['note'] ?? $payment->note,
                'updated_by'   => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Payment Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'পেমেন্ট আপডেট করতে ব্যর্থ হয়েছে।'], 500);
        }
    }

    public function deletePayment(InvoicePayment $payment)
    {
        DB::beginTransaction();
        try {
            // Rollback Invoice
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice) {
                $invoice->paid_amount -= $payment->paid_amount;
                $invoice->due_amount += $payment->paid_amount;
                $invoice->save();
            }

            // Rollback Customer
            $customer = Customer::find($payment->customer_id);
            if ($customer) {
                $customer->paid_amount -= $payment->paid_amount;
                $customer->due_amount += $payment->paid_amount;
                $customer->save();
            }

            $payment->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Payment Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'পেমেন্ট ডিলিট করতে ব্যর্থ হয়েছে।'], 500);
        }
    }

    public function collectPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'paid_amount'  => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($invoice) {
                if ($value > $invoice->due_amount) {
                    $fail('পরিশোধিত পরিমাণ বাকি পরিমাণের চেয়ে বেশি হতে পারে না।');
                }
            }],
            'payment_method' => 'required|string|in:cash,mobile_banking,check',
            'next_payment_date' => 'nullable|date|after_or_equal:payment_date',
            'account_number' => 'nullable|required_if:payment_method,mobile_banking|string|max:50',
            'check_number'   => 'nullable|required_if:payment_method,check|string|max:50',
            'note'           => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Update Invoice
            $invoice->paid_amount += $validated['paid_amount'];
            $invoice->due_amount -= $validated['paid_amount'];
            if (isset($validated['next_payment_date'])) {
                $invoice->next_payment_date = $validated['next_payment_date'];
            }
            $invoice->save();

            // Update Customer
            $customer = Customer::find($invoice->customer_id);
            if ($customer) {
                // Customer model also has paid_amount and due_amount that represent total across all invoices
                $customer->paid_amount += $validated['paid_amount'];
                $customer->due_amount -= $validated['paid_amount'];
                // Also update customer's global next payment date if provided
                if (isset($validated['next_payment_date'])) {
                    $customer->next_payment_date = $validated['next_payment_date'];
                }
                $customer->save();
            }

            // Create InvoicePayment record
            InvoicePayment::create([
                'invoice_id'   => $invoice->id,
                'customer_id'  => $invoice->customer_id,
                'payment_date' => $validated['payment_date'],
                'total_amount' => $invoice->total_amount, // Total amount of the invoice
                'paid_amount'  => $validated['paid_amount'],
                'due_amount'   => $invoice->due_amount, // Remaining due on the invoice
                'method'       => $validated['payment_method'],
                'account_number' => $validated['payment_method'] === 'mobile_banking' ? ($validated['account_number'] ?? null) : null,
                'check_number'   => $validated['payment_method'] === 'check' ? ($validated['check_number'] ?? null) : null,
                'note'         => $validated['note'] ?? null,
                'created_by'   => Auth::id(),
                'updated_by'   => Auth::id(),
            ]);

            DB::commit();

            return response()->json(['message' => 'পেমেন্ট সফলভাবে সংগ্রহ করা হয়েছে!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Collect Payment Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'পেমেন্ট সংগ্রহ করতে ব্যর্থ হয়েছে।'], 500);
        }
    }
}

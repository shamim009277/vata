<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\PaymentHead;
use App\Models\PaymentKatha;
use Illuminate\Http\Request;
use App\Models\RowProduction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaymentKhataRequest;

class PaymentKhataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = PaymentHead::active()->get();

        $query = PaymentKatha::with([
            'paymentHead:id,name',
        ])->orderBy('id', 'desc');

        $search = $request->input('search');
        $date = $request->input('date');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('paymentHead', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhere('payment_details', 'like', "%{$search}%")
                ->orWhere('payment_type', 'like', "%{$search}%");
            });
        }

        if ($date) {
            $query->whereDate('payment_date', Carbon::parse($date)->format('Y-m-d'));
        } else {
            $query->whereDate('payment_date', Carbon::now()->format('Y-m-d'));
        }
        $perPage = $request->perPage ?? 20;

        //dd($query->paginate($perPage)->withQueryString());

        return Inertia::render('payment_khata/Index', [
            'items' => $items,
            'payments' => $query->paginate($perPage)->withQueryString(),
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
    public function store(PaymentKhataRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['season'] = 2425;
            PaymentKatha::create($data);
            DB::commit();
            return redirect()->back()->with('success', 'পেমেন্ট খাতা সফলভাবে তৈরি করা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Payment Khata Create/Update Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);
            return redirect()->back()->with('error', 'পেমেন্ট খাতা তৈরি সমস্যা আছে।');
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
    public function update(PaymentKhataRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $payment = PaymentKatha::findOrFail($id);

            $data = $request->validated();
            $data['season'] = 2425;

            $payment->update($data);

            DB::commit();

            return redirect()->back()->with('success', 'পেমেন্ট খাতা সফলভাবে আপডেট করা হয়েছে!');

        } catch (\Throwable $th) {

            DB::rollBack();

            Log::error('Payment Khata Create/Update Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);

            return redirect()->back()->with('error', 'পেমেন্ট খাতা আপডেট করার সময় সমস্যা হয়েছে।');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $payment = PaymentKatha::findOrFail($id);
            $payment->delete();
            DB::commit();
            return redirect()->back()->with('success', 'পেমেন্ট খাতা সফলভাবে মুছে ফেলা হয়েছে!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Payment Khata Destroy Failed', [
                'error' => $th->getMessage(),
                'line'  => $th->getLine()
            ]);
            return redirect()->back()->with('error', 'পেমেন্ট খাতা মুছে ফেলার সময় সমস্যা হয়েছে।');
        }
    }
}

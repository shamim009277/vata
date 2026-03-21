<?php

namespace App\Http\Controllers;

use App\Models\AssetTransaction;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Load;
use App\Models\PaymentKatha;
use App\Models\RowProduction;
use App\Models\Unload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $season = $user->season;

        // Date Filtering Logic
        $filterType = $request->input('filter', 'season'); // default: season
        $customDate = $request->input('date');
        $customMonth = $request->input('month');

        $applyDateFilter = function ($query, $dateColumn) use ($filterType, $customDate, $customMonth, $season) {
            $query->where('season', $season); // Always filter by season first

            if ($customDate) {
                $query->whereDate($dateColumn, $customDate);
            } elseif ($customMonth) {
                $query->whereYear($dateColumn, Carbon::parse($customMonth)->year)
                      ->whereMonth($dateColumn, Carbon::parse($customMonth)->month);
            } else {
                switch ($filterType) {
                    case 'today':
                        $query->whereDate($dateColumn, Carbon::today());
                        break;
                    case 'last_7_days':
                        $query->whereBetween($dateColumn, [Carbon::today()->subDays(7), Carbon::today()]);
                        break;
                    case 'last_15_days':
                        $query->whereBetween($dateColumn, [Carbon::today()->subDays(15), Carbon::today()]);
                        break;
                    case 'season':
                    default:
                        // Already filtered by season
                        break;
                }
            }
        };

        // --- Cards Data ---
        
        // 1. Sales (Invoice)
        $salesQuery = Invoice::query();
        $applyDateFilter($salesQuery, 'invoice_date');
        $totalSales = $salesQuery->sum('total_amount');
        $cashSales = $salesQuery->sum('paid_amount'); // Assuming collected amount
        $dueSales = $salesQuery->sum('due_amount');

        // 2. Payments (PaymentKatha)
        $paymentQuery = PaymentKatha::query();
        $applyDateFilter($paymentQuery, 'payment_date');
        $totalPayment = $paymentQuery->sum('amount'); // Total Expense/Payment

        
        // --- Tables Data ---

        // 1. Invoice Breakdown (Category wise)
        $invoiceBreakdown = DB::table('invoice_details')
            ->join('invoices', 'invoice_details.invoice_id', '=', 'invoices.id')
            ->join('items', 'invoice_details.item_id', '=', 'items.id')
            ->select(
                'items.name as category',
                DB::raw('COUNT(DISTINCT invoices.id) as invoice_count'),
                DB::raw('SUM(invoice_details.quantity) as total_quantity'),
                DB::raw('SUM(invoice_details.amount) as total_amount')
            )
            ->where('invoices.season', $season) // Filter season on invoice
            ->groupBy('items.name');

        // Apply date filter manually to the join query
        if ($customDate) {
            $invoiceBreakdown->whereDate('invoices.invoice_date', $customDate);
        } elseif ($customMonth) {
            $invoiceBreakdown->whereYear('invoices.invoice_date', Carbon::parse($customMonth)->year)
                             ->whereMonth('invoices.invoice_date', Carbon::parse($customMonth)->month);
        } else {
            switch ($filterType) {
                case 'today':
                    $invoiceBreakdown->whereDate('invoices.invoice_date', Carbon::today());
                    break;
                case 'last_7_days':
                    $invoiceBreakdown->whereBetween('invoices.invoice_date', [Carbon::today()->subDays(7), Carbon::today()]);
                    break;
                case 'last_15_days':
                    $invoiceBreakdown->whereBetween('invoices.invoice_date', [Carbon::today()->subDays(15), Carbon::today()]);
                    break;
            }
        }
        $invoiceTable = $invoiceBreakdown->get();


        // 2. Payment Breakdown (Head wise)
        $paymentBreakdown = PaymentKatha::query()->with('paymentHead');
        $applyDateFilter($paymentBreakdown, 'payment_date');
        $paymentTable = $paymentBreakdown->select(
                'payment_head_id',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(paid_amount) as total_paid')
            )
            ->groupBy('payment_head_id')
            ->get()
            ->map(function($item) {
                return [
                    'head_name' => $item->paymentHead->name ?? 'Unknown',
                    'amount' => $item->total_amount,
                    'paid' => $item->total_paid
                ];
            });


        // 3. Production Breakdown (Field wise)
        $productionBreakdown = RowProduction::query()->with('field');
        $applyDateFilter($productionBreakdown, 'product_date');
        $productionTable = $productionBreakdown->select(
                'field_list_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('field_list_id')
            ->get()
            ->map(function($item) {
                return [
                    'field_name' => $item->field->name ?? 'Unknown',
                    'quantity' => $item->total_quantity
                ];
            });


        // 4. Delivery Breakdown (Item wise)
        $deliveryBreakdown = Delivery::query()->with('item');
        $applyDateFilter($deliveryBreakdown, 'delivery_date');
        $deliveryTable = $deliveryBreakdown->select(
                'item_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('item_id')
            ->get()
            ->map(function($item) {
                return [
                    'item_name' => $item->item->name ?? 'Unknown',
                    'quantity' => $item->total_quantity
                ];
            });

        
        // 5. Load Breakdown (Item wise)
        $loadBreakdown = Load::query()->with('item');
        $applyDateFilter($loadBreakdown, 'loading_date');
        $loadTable = $loadBreakdown->select(
                'item_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('item_id')
            ->get()
            ->map(function($item) {
                return [
                    'item_name' => $item->item->name ?? 'Unknown',
                    'quantity' => $item->total_quantity
                ];
            });


        // 6. Unload Breakdown (Item wise via details)
        // Since Unload has many UnloadDetails, we need to join or aggregate properly.
        // Assuming Unload has `unload_date` and `season`.
        $unloadBreakdown = DB::table('unload_details')
            ->join('unloads', 'unload_details.unload_id', '=', 'unloads.id')
            ->join('items', 'unload_details.item_id', '=', 'items.id')
            ->select(
                'items.name as item_name',
                DB::raw('SUM(unload_details.quantity) as total_quantity')
            )
            ->where('unloads.season', $season)
            ->groupBy('items.name');

         // Apply date filter to unloads table
         if ($customDate) {
            $unloadBreakdown->whereDate('unloads.unload_date', $customDate);
        } elseif ($customMonth) {
            $unloadBreakdown->whereYear('unloads.unload_date', Carbon::parse($customMonth)->year)
                            ->whereMonth('unloads.unload_date', Carbon::parse($customMonth)->month);
        } else {
            switch ($filterType) {
                case 'today':
                    $unloadBreakdown->whereDate('unloads.unload_date', Carbon::today());
                    break;
                case 'last_7_days':
                    $unloadBreakdown->whereBetween('unloads.unload_date', [Carbon::today()->subDays(7), Carbon::today()]);
                    break;
                case 'last_15_days':
                    $unloadBreakdown->whereBetween('unloads.unload_date', [Carbon::today()->subDays(15), Carbon::today()]);
                    break;
            }
        }
        $unloadTable = $unloadBreakdown->get();


        return Inertia::render('Dashboard1', [
            'stats' => [
                'total_sales' => $totalSales,
                'cash_sales' => $cashSales,
                'due_sales' => $dueSales,
                'total_payment' => $totalPayment,
            ],
            'tables' => [
                'invoice' => $invoiceTable,
                'payment' => $paymentTable,
                'production' => $productionTable,
                'delivery' => $deliveryTable,
                'load' => $loadTable,
                'unload' => $unloadTable,
            ],
            'filters' => [
                'filter' => $filterType,
                'date' => $customDate,
                'month' => $customMonth,
            ]
        ]);
    }

    public function session_get(){
        $user = Auth::user();
        return response()->json([
            'season' => $user->season
        ]);
    }

    public function sessionChange(Request $request){
        $user = Auth::user();
        $user->season = $request->season;
        $user->save();

        return response()->json([
            'message' => 'সিজন সফলভাবে পরিবর্তন করা হয়েছে',
        ]);
    }   
}

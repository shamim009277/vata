<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\BusinessStore;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Mpdf\Mpdf;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:reports.delivery', only: ['deliveryReport', 'deliveryReportPdf', 'challanReport', 'challanReportPdf']),
        ];
    }

    public function deliveryReport(Request $request)
    {
        $query = Delivery::query()->with(['customer', 'item', 'invoice']);

        if ($request->filled('date')) {
            $query->whereDate('delivery_date', $request->date);
        }

        if ($request->filled('month')) {
            // Assuming month input is 'YYYY-MM'
            $parts = explode('-', $request->month);
            if (count($parts) == 2) {
                $query->whereYear('delivery_date', $parts[0])
                      ->whereMonth('delivery_date', $parts[1]);
            }
        }

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        // Get all seasons for filter dropdown
        $seasons = Delivery::select('season')->distinct()->whereNotNull('season')->pluck('season');

        $deliveries = $query->latest()->get();

        return Inertia::render('Reports/DeliveryReport', [
            'deliveries' => $deliveries,
            'filters' => $request->only(['date', 'month', 'season']),
            'seasons' => $seasons,
            'business_store' => BusinessStore::first(),
        ]);
    }

    public function deliveryReportPdf(Request $request)
    {
        $query = Delivery::query()->with(['customer', 'item', 'invoice']);

        if ($request->filled('date')) {
            $query->whereDate('delivery_date', $request->date);
        }

        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) == 2) {
                $query->whereYear('delivery_date', $parts[0])
                      ->whereMonth('delivery_date', $parts[1]);
            }
        }

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        $deliveries = $query->latest()->get();

        $html = view('reports.delivery_pdf', [
            'deliveries' => $deliveries,
            'filters' => $request->only(['date', 'month', 'season']),
            'business_store' => BusinessStore::first(),
        ])->render();

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [
                storage_path('fonts'),
            ]),
            'fontdata' => $fontData + [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ]
            ],
            'default_font' => 'solaimanlipi',
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('delivery_report.pdf', 'I');
    }

    public function challanReport(Request $request)
    {
        $query = Invoice::query()->with(['customer']);

        if ($request->filled('date')) {
            $query->whereDate('invoice_date', $request->date);
        }

        if ($request->filled('month')) {
            // Assuming month input is 'YYYY-MM'
            $parts = explode('-', $request->month);
            if (count($parts) == 2) {
                $query->whereYear('invoice_date', $parts[0])
                      ->whereMonth('invoice_date', $parts[1]);
            }
        }

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        // Get all seasons for filter dropdown
        $seasons = Invoice::select('season')->distinct()->whereNotNull('season')->pluck('season');

        $invoices = $query->latest()->get();

        return Inertia::render('Reports/ChallanReport', [
            'invoices' => $invoices,
            'filters' => $request->only(['date', 'month', 'season']),
            'seasons' => $seasons,
            'business_store' => BusinessStore::first(),
        ]);
    }

    public function challanReportPdf(Request $request)
    {
        $query = Invoice::query()->with(['customer']);

        if ($request->filled('date')) {
            $query->whereDate('invoice_date', $request->date);
        }

        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) == 2) {
                $query->whereYear('invoice_date', $parts[0])
                      ->whereMonth('invoice_date', $parts[1]);
            }
        }

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        $invoices = $query->latest()->get();

        $html = view('reports.challan_pdf', [
            'invoices' => $invoices,
            'filters' => $request->only(['date', 'month', 'season']),
            'business_store' => BusinessStore::first(),
        ])->render();

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [
                storage_path('fonts'),
            ]),
            'fontdata' => $fontData + [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ]
            ],
            'default_font' => 'solaimanlipi',
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('challan_report.pdf', 'I');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\BusinessStore;
use App\Models\RowProduction;
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
            new Middleware('permission:reports.delivery', only: ['deliveryReport', 'deliveryReportPdf', 'challanReport', 'challanReportPdf', 'rawBrickProductionReport', 'rawBrickProductionReportPdf']),
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

        $banglaFontPath = storage_path('fonts/SolaimanLipi.ttf');
        $mpdfConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'tempDir' => storage_path('app/temp'), // Ensure tempDir is writable
        ];
        if (is_file($banglaFontPath)) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];
            $mpdfConfig['fontDir'] = array_merge($fontDirs, [storage_path('fonts')]);
            $mpdfConfig['fontdata'] = $fontData + [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ];
            $mpdfConfig['default_font'] = 'solaimanlipi';
        } else {
            // Fallback to a safe default if custom font is unavailable/corrupted
            $mpdfConfig['default_font'] = 'sans-serif';
        }
        $mpdf = new Mpdf($mpdfConfig);

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

        $banglaFontPath = storage_path('fonts/SolaimanLipi.ttf');
        $mpdfConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'tempDir' => storage_path('app/temp'), // Ensure tempDir is writable
        ];
        if (is_file($banglaFontPath)) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];
            $mpdfConfig['fontDir'] = array_merge($fontDirs, [storage_path('fonts')]);
            $mpdfConfig['fontdata'] = $fontData + [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ];
            $mpdfConfig['default_font'] = 'solaimanlipi';
        } else {
            $mpdfConfig['default_font'] = 'sans-serif';
        }
        $mpdf = new Mpdf($mpdfConfig);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('challan_report.pdf', 'I');
    }

    public function rawBrickProductionReport(Request $request)
    {
        $query = RowProduction::query()->with(['field']);

        if ($request->filled('date')) {
            $query->whereDate('product_date', $request->date);
        }

        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) == 2) {
                $query->whereYear('product_date', $parts[0])
                      ->whereMonth('product_date', $parts[1]);
            }
        }

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        $seasons = RowProduction::select('season')->distinct()->whereNotNull('season')->pluck('season');

        $productions = $query->latest()->get();

        return Inertia::render('Reports/RawBrickProductionReport', [
            'productions' => $productions,
            'filters' => $request->only(['date', 'month', 'season']),
            'seasons' => $seasons,
            'business_store' => BusinessStore::first(),
        ]);
    }

    public function rawBrickProductionReportPdf(Request $request)
    {
        $query = RowProduction::query()->with(['field']);

        if ($request->filled('date')) {
            $query->whereDate('product_date', $request->date);
        }

        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) == 2) {
                $query->whereYear('product_date', $parts[0])
                      ->whereMonth('product_date', $parts[1]);
            }
        }

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        $productions = $query->latest()->get();

        $html = view('reports.raw_brick_production_pdf', [
            'productions' => $productions,
            'filters' => $request->only(['date', 'month', 'season']),
            'business_store' => BusinessStore::first(),
        ])->render();

        $banglaFontPath = storage_path('fonts/SolaimanLipi.ttf');
        $mpdfConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'tempDir' => storage_path('app/temp'),
        ];
        if (is_file($banglaFontPath)) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];
            $mpdfConfig['fontDir'] = array_merge($fontDirs, [storage_path('fonts')]);
            $mpdfConfig['fontdata'] = $fontData + [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ];
            $mpdfConfig['default_font'] = 'solaimanlipi';
        } else {
            $mpdfConfig['default_font'] = 'sans-serif';
        }
        $mpdf = new Mpdf($mpdfConfig);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('raw_brick_production_report.pdf', 'I');
    }
}

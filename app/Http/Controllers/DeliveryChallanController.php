<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DeliveryChallanController extends Controller
{
    public function create(Request $request)
    {
        $customerId = $request->input('customer_id');
        $customer = null;
        $invoices = [];

        if ($customerId) {
            $customer = Customer::findOrFail($customerId);
            // Fetch invoices for this customer
            $invoices = Invoice::where('customer_id', $customerId)
                ->orderBy('id', 'desc')
                ->get();
        }

        return Inertia::render('delivery-challan/Create', [
            'customer' => $customer,
            'invoices' => $invoices,
        ]);
    }

    public function getInvoiceItems($invoiceNo)
    {
        $invoice = Invoice::where('invoice_no', $invoiceNo)->firstOrFail();
        
        $details = InvoiceDetails::where('invoice_id', $invoice->id)
            ->with('item')
            ->get();
            
        return response()->json($details);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_no' => 'required|exists:invoices,invoice_no',
            'delivery_date' => 'required|date',
            'items' => 'required|array',
            'items.*.current_delivery' => 'nullable|numeric|min:0',
        ]);

        $invoice = Invoice::where('invoice_no', $request->invoice_no)->firstOrFail();
        
        // Generate Delivery No
        // Format: DEL-YY-XXXX
        $currentYear = Carbon::now()->format('y');
        $lastDelivery = Delivery::orderBy('id', 'desc')->first();
        $lastId = $lastDelivery ? $lastDelivery->id : 0;
        $deliveryNo = $currentYear . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $invoice, $deliveryNo) {
            foreach ($request->items as $itemData) {
                $qty = $itemData['current_delivery'] ?? 0;
                
                if ($qty > 0) {
                    // Create Delivery Record
                    $delivery = new Delivery();
                    // Auto-filled by boot method usually, but let's be explicit if needed or rely on model boot
                    // Delivery model boot sets business_id, store_id from Auth user.
                    // But if admin is creating, it might be different. 
                    // Let's rely on model boot for creator/store info if it uses Auth::user()
                    
                    $delivery->season = $invoice->season;
                    $delivery->delivery_no = $deliveryNo;
                    $delivery->delivery_date = $request->delivery_date;
                    $delivery->invoice_no = $invoice->invoice_no;
                    $delivery->customer_id = $request->customer_id;
                    $delivery->item_id = $itemData['item_id'];
                    $delivery->address = $invoice->customer->address ?? '';
                    $delivery->driver_name = $request->driver_name;
                    $delivery->truck_number = $request->truck_number;
                    $delivery->quantity = $itemData['order_qty']; // Total Ordered
                    $delivery->delivery_qty = $qty; // This Delivery Qty
                    $delivery->note = $request->note;
                    $delivery->is_active = true;
                    
                    $delivery->save();

                    // Update Invoice Details Delivery Quantity
                    $detail = InvoiceDetails::where('invoice_id', $invoice->id)
                        ->where('item_id', $itemData['item_id'])
                        ->first();
                    
                    if ($detail) {
                        $detail->increment('delivery_quantity', $qty);
                    }
                }
            }
        });

        return redirect()->route('customers.show', $request->customer_id)
            ->with('success', 'ডেলিভারি চালান সফলভাবে তৈরি হয়েছে');
    }
}

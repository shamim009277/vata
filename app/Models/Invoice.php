<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\InvoiceDetails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    protected $fillable = [
        'business_id',
        'store_id',
        'season',
        'customer_id',
        'invoice_no',
        'invoice_date',
        'delivery_date',
        'invoice_type',
        'quantity',
        'sub_total',
        'total_amount',
        'paid_amount',
        'due_amount',
        'discount',
        'rant',
        'next_payment_date',
        'note',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'delivery_date' => 'date',
        'next_payment_date' => 'date',
    ];

    public function customer():BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoiceDetails():HasMany
    {
        return $this->hasMany(InvoiceDetails::class,'invoice_id','id');
    }

    public function deliveries():HasMany
    {
        return $this->hasMany(Delivery::class,'invoice_no','invoice_no');
    }

    public function creator():BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($invoice) {
            $user_business = BusinessStore::where('user_id', Auth::user()->id)->first();
            $invoice->created_by = Auth::user()->id;
            $invoice->updated_by = Auth::user()->id;
            $invoice->business_id = $user_business->business_id;
            $invoice->store_id = $user_business->id;
        });

        static::updating(function ($invoice) {
            $invoice->updated_by = Auth::user()->id;
        });

        static::addGlobalScope('filterByRole', function (Builder $builder) {
            if (Auth::check()) {
                if (Auth::user()->role === 'admin') {
                    return $builder;
                }else{
                    return $builder->where('store_id', Auth::user()->store_id);
                }
            }
        });
    }
}

<?php

namespace App\Models;

use App\Models\BusinessStore;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Delivery extends Model
{

    protected $fillable = [
        'business_id',    
        'store_id',    
        'season',    
        'delivery_no',    
        'delivery_date',    
        'invoice_no',    
        'customer_id',    
        'item_id',    
        'address',    
        'driver_name',    
        'driver_phone',    
        'truck_number',    
        'quantity',    
        'rate',    
        'amount',    
        'delivery_qty',    
        'delivery_rant',    
        'note',    
        'is_active',    
        'created_by',    
        'updated_by'
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class,'item_id','id');
    }
    
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class,'invoice_no','invoice_no');
    }

    public static function boot()
    {
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
                } else {
                    return $builder->where('store_id', Auth::user()->store_id);
                }
            }
        });
    }
}

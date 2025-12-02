<?php

namespace App\Models;

use App\Models\Business;
use App\Models\BusinessStore;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
    protected $fillable = [
        'business_id',
        'store_id',    
        'season',    
        'name',    
        'phone',    
        'address',    
        'total_item',       
        'delivery_item',    
        'total_amount',    
        'paid_amount',    
        'due_amount',    
        'next_delivery_date',    
        'next_payment_date',    
        'is_active',    
        'created_by',    
        'updated_by'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function store()
    {
        return $this->belongsTo(BusinessStore::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            $user_business = BusinessStore::where('user_id', Auth::user()->id)->first();
            $customer->created_by = Auth::user()->id;
            $customer->updated_by = Auth::user()->id;
            $customer->business_id = $user_business->business_id;
            $customer->store_id = $user_business->id;
        });

        static::updating(function ($customer) {
            $customer->updated_by = Auth::user()->id;
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

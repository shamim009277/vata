<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\BusinessStore;

class PaymentHead extends Model
{

    protected $fillable = [
        'business_id',
        'store_id',
        'name',
        'group',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public static function boot()
    {
        parent::boot();

        static::creating(function ($payment_head) {
            $user_business = BusinessStore::where('user_id', Auth::user()->id)->first();
            $payment_head->created_by = Auth::user()->id;
            $payment_head->updated_by = Auth::user()->id;
            $payment_head->business_id = $user_business->business_id;
            $payment_head->store_id = $user_business->id;
        });

        static::updating(function ($payment_head) {
            $payment_head->updated_by = Auth::user()->id;
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

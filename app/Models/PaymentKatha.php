<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\BusinessStore;

class PaymentKatha extends Model
{
    protected $fillable = [
        'business_id',
        'store_id',
        'season',
        'payment_head_id',
        'payment_type',
        'payment_date',
        'payment_details',
        'quantity',
        'amount',
        'paid_amount',
        'due_amount',
        'is_active',
        'is_locked',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'payment_date' => 'date',
    ];

    public function paymentHead(): BelongsTo
    {
        return $this->belongsTo(PaymentHead::class,'payment_head_id','id');
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($payment_katha) {
            $user_business = BusinessStore::where('user_id', Auth::user()->id)->first();
            $payment_katha->created_by = Auth::user()->id;
            $payment_katha->updated_by = Auth::user()->id;
            $payment_katha->business_id = $user_business->business_id;
            $payment_katha->store_id = $user_business->id;
        });

        static::updating(function ($payment_katha) {
            $payment_katha->updated_by = Auth::user()->id;
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

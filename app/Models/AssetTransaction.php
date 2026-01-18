<?php

namespace App\Models;

use App\Models\BusinessStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AssetTransaction extends Model
{
    protected $table = 'asset_transactions';
    
    protected $fillable = [
        "business_id",
        "store_id",
        "season",
        "date",
        "product_name",
        "product_category",
        "quantity",    
        "status",    
        "is_active",    
        "created_by",    
        "updated_by"
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($transaction) {
            $user_business = BusinessStore::where('user_id', Auth::user()->id)->first();
            $transaction->created_by = Auth::user()->id;
            $transaction->updated_by = Auth::user()->id;
            $transaction->business_id = $user_business->business_id;
            $transaction->store_id = $user_business->id;
        });

        static::updating(function ($transaction) {
            $transaction->updated_by = Auth::user()->id;
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

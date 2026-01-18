<?php

namespace App\Models;

use App\Models\BusinessStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AssetLost extends Model
{

    protected $table = "asset_losts";

    protected $fillable = [
        "business_id",
        "store_id",
        "season",
        "issue_id",
        "product_name",
        "product_category",
        "reported",
        "lost_date",
        "quantity",
        "is_active",
        "created_by",
        "updated_by"
    ];

    public static function boot()
    {
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
                } else {
                    return $builder->where('store_id', Auth::user()->store_id);
                }
            }
        });
    }
}

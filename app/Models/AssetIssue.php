<?php

namespace App\Models;

use App\Models\AssetLost;
use App\Models\Business;
use App\Models\BusinessStore;
use App\Models\NewAssetStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;


class AssetIssue extends Model
{
    protected $table = "asset_issues";

    protected $fillable = [
        "business_id",    
        "store_id",    
        "season",    
        "stock_id",    
        "product_name",    
        "product_category",    
        "to_whom",    
        "issue_date",    
        "location",    
        "quantity",    
        "is_active",    
        "created_by",    
        "updated_by"
    ];

    public function stock():BelongsTo
    {
        return $this->belongsTo(NewAssetStock::class, 'stock_id', 'id');
    }

    public function losts():hasMany
    {
        return $this->hasMany(AssetLost::class);
    }

    public function ideles(): hasMany
    {
        return $this->hasMany(IdelAsset::class);
    }

    public function business():BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    public function store():BelongsTo
    {
        return $this->belongsTo(BusinessStore::class, 'store_id', 'id');
    }

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

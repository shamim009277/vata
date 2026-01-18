<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class IdelAsset extends Model
{
    protected $table = "idel_assets";
    
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
    
    public function issue()
    {
        return $this->belongsTo(AssetIssue::class, 'issue_id', 'id');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($issue) {
            $user_business = BusinessStore::where('user_id', Auth::id())->first();
            $issue->created_by = Auth::id();
            $issue->updated_by = Auth::id();
            $issue->business_id = $user_business->business_id;
            $issue->store_id = $user_business->id;
        });

        static::updating(function ($issue) {
            $issue->updated_by = Auth::id();
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

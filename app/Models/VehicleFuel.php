<?php

namespace App\Models;

use App\Models\BusinessStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class VehicleFuel extends Model
{
    protected $fillable = [
        'business_id',
        'store_id',
        'season',
        'vehicle_id',
        'date',
        'amount',
        'quantity',
        'fuel_type',
        'voucher_no',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $user_business = BusinessStore::where('user_id', Auth::user()->id)->first();
            $model->created_by = Auth::user()->id;
            $model->updated_by = Auth::user()->id;
            $model->business_id = $user_business->business_id;
            $model->store_id = $user_business->id;
            $model->season = Auth::user()->season;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id;
        });

        static::addGlobalScope('filterByRole', function (Builder $builder) {
            if (Auth::check()) {
                if (Auth::user()->role === 'admin') {
                    return $builder;
                }else{
                    return $builder->where('store_id', Auth::user()->store_id)
                                   ->where('season', Auth::user()->season);
                }
            }
        });
    }
}

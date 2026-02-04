<?php

namespace App\Models;

use App\Models\BusinessStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Vehicle extends Model
{
    protected $fillable = [
        'business_id',
        'store_id',
        'season',
        'name',
        'number',
        'driver_name',
        'driver_phone',
        'status',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(VehicleTrip::class);
    }

    public function fuels(): HasMany
    {
        return $this->hasMany(VehicleFuel::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(VehicleService::class);
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

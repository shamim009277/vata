<?php

namespace App\Models;

use App\Models\BusinessStore;
use App\Models\UnloadDetails;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Unload extends Model
{
    protected $table = "unloads";

    protected $fillable = [
        'business_id',    
        'store_id',    
        'season',    
        'loading_date',    
        'unload_date',    
        'round',    
        'load_quantity',    
        'note',    
        'is_active',    
        'is_locked',    
        'created_by',    
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'loading_date' => 'date',
        'unload_date' => 'date'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(UnloadDetails::class, 'unload_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($unload) {
            $user_business = BusinessStore::where('user_id', Auth::user()->id)->first();
            $unload->created_by = Auth::user()->id;
            $unload->updated_by = Auth::user()->id;
            $unload->business_id = $user_business->business_id;
            $unload->store_id = $user_business->id;
        });

        static::updating(function ($unload) {
            $unload->updated_by = Auth::user()->id;
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

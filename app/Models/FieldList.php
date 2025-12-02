<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FieldList extends Model
{
    protected $fillable = [
        'business_id',
        'store_id',
        'name',
        'location',
        'stock_qty',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function store()
    {
        return $this->belongsTo(BusinessStore::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($field_list) {
            $user_business = BusinessStore::where('user_id', Auth::user()->id)->first();
            $field_list->created_by = Auth::user()->id;
            $field_list->updated_by = Auth::user()->id;
            $field_list->business_id = $user_business->business_id;
            $field_list->store_id = $user_business->id;
        });

        static::updating(function ($field_list) {
            $field_list->updated_by = Auth::user()->id;
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

<?php

namespace App\Models;

use App\Models\BusinessStore;
use App\Models\FieldList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RowProduction extends Model
{
    protected $fillable = [
        'business_id',
        'store_id',
        'season',
        'field_list_id',
        'product_date',
        'quantity',
        'is_locked',
        'is_active',
        'note',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'product_date' => 'date',
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(FieldList::class,'field_list_id','id');
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($invoice) {
            $user_business = BusinessStore::where('user_id', Auth::user()->id)->first();
            $invoice->created_by = Auth::user()->id;
            $invoice->updated_by = Auth::user()->id;
            $invoice->business_id = $user_business->business_id;
            $invoice->store_id = $user_business->id;
        });

        static::updating(function ($invoice) {
            $invoice->updated_by = Auth::user()->id;
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

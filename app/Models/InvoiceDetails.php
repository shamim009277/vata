<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class InvoiceDetails extends Model
{
    protected $fillable = [
        'invoice_id',
        'item_id',
        'quantity',
        'delivery_quantity',
        'amount',
        'rate',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice():BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item():BelongsTo
    {
        return $this->belongsTo(Item::class,'item_id','id');
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($invoiceDetails) {
            $invoiceDetails->created_by = Auth::user()->id;
            $invoiceDetails->updated_by = Auth::user()->id;
        });

        static::updating(function ($invoiceDetails) {
            $invoiceDetails->updated_by = Auth::user()->id;
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

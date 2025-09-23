<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemStock extends Model
{
    protected $fillable = ['item_id','stock_in','stock_out','current_stock','unit','is_active'];

    public function scopeActive($query){
        return $query->where('is_active',1);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}

<?php

namespace App\Models;

use App\Models\Item;
use App\Models\Unload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnloadDetails extends Model
{
    protected $table = 'unload_details';

    protected $fillable = [
        'unload_id','item_id','quantity','is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function unload(): BelongsTo
    {
        return $this->belongsTo(Unload::class, 'unload_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

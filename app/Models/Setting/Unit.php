<?php

namespace App\Models\Setting;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'code',
        'conversion_rate',
        'root_id',
        'is_active',
        'is_root',
        'unit_standards',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    public function root(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'root_id');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($unit) {
            $unit->created_by = Auth::id();
            $unit->updated_by = Auth::id();
        });

        static::updating(function ($unit) {
            $unit->updated_by = Auth::id();
        });
    }

    public function getWeightStandardAttribute()
    {
        $map = [
            'W' => 'Weight',
            'V' => 'Volume',
            'L' => 'Length',
            'Q' => 'Quantity',
        ];

        return $map[$this->unit_standards] ?? 'Unknown';
    }

    public function scopeActive($query){
        return $query->where('is_active',1);
    }

    public function scopeInactive($query){
        return $query->where('is_active',0);
    }
}

<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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


    public function root()
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
}

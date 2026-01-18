<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Round extends Model
{
    protected $table = 'rounds';

    protected $fillable = [
        'round','is_active','created_by','updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($invoice) {
            $invoice->created_by = Auth::user()->id;
            $invoice->updated_by = Auth::user()->id;
        });

        static::updating(function ($invoice) {
            $invoice->updated_by = Auth::user()->id;
        });
    }
}

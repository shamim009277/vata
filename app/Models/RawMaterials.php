<?php

namespace App\Models;

use App\Models\Setting\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawMaterials extends Model
{
    protected $fillable = ['name','type','unit_id','cost_per_unit','stock_alert_quantity','is_active'];

    public function scopeActive($query){
        return $query->where('is_active',1);
    }

    public function scopeInactive($query){
        return $query->where('is_active',0);
    }

    public static function boot(){
        parent::boot();
        static::creating(function($model){
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });
        static::updating(function($model){
            $model->updated_by = Auth::id();
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}

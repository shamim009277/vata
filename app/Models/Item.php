<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = ['name','unit_id','price_per_unit','is_active','created_by','updated_by'];

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
        return $this->belongsTo(Unit::class,'unit_id','id');
    }
}

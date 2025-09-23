<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawMaterialStock extends Model
{
    protected $fillable = ['raw_material_id','stock_in','stock_out','current_stock','unit','is_active'];

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

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterials::class, 'raw_material_id', 'id');
    }
}

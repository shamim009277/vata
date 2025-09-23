<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = ['year','date','supplier_id','total_amount','paid_amount','due_amount','note','is_active'];

    public function scopeActive($query){
        return $query->where('is_active',1);
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function purchaseDetails(){
        return $this->hasMany(PurchaseItems::class, 'purchase_id', 'id');
    }
}

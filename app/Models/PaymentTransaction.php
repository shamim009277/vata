<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['year','date','purchase_id','amount','payment_method','note','is_active'];

    public function scopeActive($query){
        return $query->where('is_active',1);
    }

    public function purchase(){
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

}

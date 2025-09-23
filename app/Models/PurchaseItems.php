<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItems extends Model
{
    protected $fillable = ['purchase_id','item_id','quantity','price','total'];

    public function purchase(){
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    public function item(){
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}

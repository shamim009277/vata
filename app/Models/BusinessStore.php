<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessStore extends Model
{
    protected $fillable = ['user_id','business_id','store_name','short_name','store_name_en','address','phone','owner_name','owner_phone','is_active'];

    public function scopeActive($query){
        return $query->where('is_active',1);
    }

    public function scopeInactive($query){
        return $query->where('is_active',0);
    }
}

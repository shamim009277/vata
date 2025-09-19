<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessStore extends Model
{
    protected $fillable = ['user_id','business_id','store_name','email','address','phone','is_active'];

    public function scopeActive($query){
        return $query->where('is_active',1);
    }

    public function scopeInactive($query){
        return $query->where('is_active',0);
    }
}

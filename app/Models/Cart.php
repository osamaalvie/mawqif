<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Cart extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'session_id', 'user_id','product_id','qty'
    ];

    public function products()
    {
        return $this->hasMany(Product::class,'cart_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}

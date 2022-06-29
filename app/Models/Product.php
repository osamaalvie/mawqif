<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $timestamps = true;
    protected $fillable = ['name','price','category_id','description','avatar','created_at','updated_at'];

    protected $hidden = [];
}

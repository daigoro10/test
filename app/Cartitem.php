<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cartitem extends Model
{
    protected $fillable = ['quantity'];

    public function product()
    {
        return $this->belongsTo('App\Product');
    }

    public function cart()
    {
        return $this->belongsTo('App\Cart');
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function cartitems()
    {
        return $this->hasMany('App\Cartitem');
    }

    public function favorites()
    {
        return $this->hasMany('App\Favorite');
    }

    public function purchases()
    {
        return $this->hasMany('App\Purchase');
    }
}

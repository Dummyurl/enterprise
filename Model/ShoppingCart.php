<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    protected $table = "shoppingcart";
    public $primaryKey  = 'id';
    public $timestamps = true;

    public function pack(){
        return $this->hasOne('App\Model\Package','package_id','package_id_fk');
    }
}

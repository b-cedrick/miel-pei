<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingcartProducts extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "quantity",
        "shoppingcart_id",
        "product_id",
    ];

    public function shoppingcart()
    {
        return $this->belongsTo('App\Models\Shoppingcart', 'shoppingcart_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Products', 'product_id', 'id');
    }
}

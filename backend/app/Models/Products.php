<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'price',
        'quantity',
        'image',
        'amountSell'
    ];

    public function user()
    {
        return $this->hasMany('App\Models\Producer');
    }

    public function shoppingcartRow()
    {
        return $this->hasMany('App\Models\ShoppingcartProducts');
    }

    public function orderRow() 
    {
        return $this->hasMany('App\Models\OrderProduct');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'state',
        'delivery',
        'billing',
        'finished_at',
    ];

    public function users()
    {
        return $this->hasMany('App\Models\UserOrder', 'order_id', 'id');
    }

    public function products()
    {
        return $this->hasMany('App\Models\OrderProduct', 'order_id', 'id');
    }

    public function invoice()
    {
        return $this->hasOne('App\Models\Invoice');
    }
}

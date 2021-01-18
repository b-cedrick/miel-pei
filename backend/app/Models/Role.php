<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'label' ];

    /**
     * Return roles that user's have
     * 1 role can have many users
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }
}

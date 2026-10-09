<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = ['name', 'area', 'status'];

    public function currentOrder()
    {
        return $this->hasOne(Order::class)->where('status', 'pending')->latestOfMany();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

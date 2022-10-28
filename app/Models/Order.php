<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function user_orders()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'id');
    }

    // public function user_payments()
    // {
    //     return $this->hasMany(OrderPayment::class, 'order_id', 'id');
    // }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }
}

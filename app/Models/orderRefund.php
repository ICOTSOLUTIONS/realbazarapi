<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class orderRefund extends Model
{
    use HasFactory;

    public function orders()
    {
        return $this->belongsTo(Order::class, 'id', 'order_id');
    }
}

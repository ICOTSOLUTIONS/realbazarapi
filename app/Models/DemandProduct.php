<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandProduct extends Model
{
    use HasFactory;

    public function demand_image()
    {
        return $this->hasMany(DemandProductImage::class, 'demand_product_id', 'id');
    }
}

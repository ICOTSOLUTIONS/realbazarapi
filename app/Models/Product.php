<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category' ,'status'];
    // public function color(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => json_decode($value),
    //         set: fn ($value) => json_encode($value)
    //     );
    // }

    // public function size(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => json_decode($value),
    //         set: fn ($value) => json_encode($value)
    //     );
    // }

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class,'product_id','id');
    }
    public function subCategories()
    {
        return $this->belongsTo(SubCategory::class,'sub_category_id','id');
    }
    public function orders()
    {
        return $this->hasMany(OrderProduct::class,'product_id','id');
    }
    public function history()
    {
        return $this->hasMany(UserProductHistory::class,'product_id','id');
    }
}

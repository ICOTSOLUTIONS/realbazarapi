<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category', 'status'];
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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }
    public function subCategories()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id', 'id');
    }
    public function orders()
    {
        return $this->hasMany(OrderProduct::class, 'product_id', 'id');
    }
    public function history()
    {
        return $this->hasMany(UserProductHistory::class, 'product_id', 'id');
    }
    public function likes()
    {
        return $this->hasMany(LikeProduct::class, 'product_id', 'id');
    }
    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        if (auth()->check()) {
            $user = auth()->user()->load('role');
            if ($user->role->name == 'user') {
                static::addGlobalScope('active', function ($builder) {
                    $builder->where('status', 'approved')->where('is_delete', false)->whereRelation('user', 'is_block', false);
                });
            } elseif ($user->role->name == 'admin') {
                static::addGlobalScope('active', function ($builder) {
                    $builder->whereRelation('user', 'is_block', false);
                });
            } else {
                static::addGlobalScope('active', function ($builder) {
                    $builder->whereRelation('user', 'is_block', false)->where('is_delete', false);
                });
            }
        } else {
            static::addGlobalScope('active', function ($builder) {
                $builder->where('status', 'approved')->where('is_delete', false)->whereRelation('user', 'is_block', false);
            });
        }
    }
}

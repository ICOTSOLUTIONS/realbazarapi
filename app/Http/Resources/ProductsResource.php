<?php

namespace App\Http\Resources;

use App\Models\ProductReview;
use Illuminate\Http\Resources\Json\JsonResource;
use PHPUnit\Framework\Constraint\Count;

class ProductsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            // 'brand' => $this->brand,
            // 'color' => $this->color,
            'price' => $this->price,
            'discount' => $this->discount_price,
            'product_description' => $this->desc,
            'tags' => $this->tags,
            'product_status' => $this->status,
            'is_delete' => $this->is_delete,
            'is_featured' => $this->is_featured,
            'is_new_arrival' => $this->is_new_arrival,
            // 'size' => $this->size,
            'image' => $this->images,
            'shop' => $this->user,
            'category' => $this->subCategories->categories,
            'sub_category' => $this->subCategories,
            'followers' => $this->user->follow,
            'likes' => $this->likes,
            // 'reviews' => ProductReview::with('users')->where('product_id',$this->id)->get(),
            'reviews' => $this->reviews,
            'rating' => $this->reviews->sum('stars')/5,
            'totalReviews' => $this->reviews->count(),
            'totalLikes' => $this->likes->count(),
            'totalFollowers' => $this->user->follow->count(),
        ];
    }
}

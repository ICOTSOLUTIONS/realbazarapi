<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_id' => $this->order_id,
            'customer_name' => $this->customer_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'delivery_address' => $this->delivery_address,
            'order_date' => $this->order_date,
            'status' => $this->status,
            'user' => $this->users,
            'seller' => $this->seller,
            'order_products' => $this->user_orders,
            'payment' => $this->user_payments,
        ];
    }
}

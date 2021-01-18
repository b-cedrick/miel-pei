<?php

namespace App\Http\Resources;

use App\Models\OrderProduct;
use DateTime;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->finished_at != null) {
            $finishedAt = new DateTime($this->finished_at);
            $finishedAtFormated = $finishedAt->format('d-m-Y H:i:s');
        } else {
            $finishedAtFormated = null;
        }

        $createdAt = new DateTime($this->created_at);
        $createdAtFormated = $createdAt->format('d-m-Y H:i:s');

        $updatedAt = new DateTime($this->updated_at);
        $updatedAtFormated = $updatedAt->format('d-m-Y H:i:s');

        $productList = OrderProduct::where(['order_id' => $this->id])->get();

        return [
            'id'          => $this->id,
            'state'       => $this->state,
            'delivery'    => $this->delivery,
            'billing'     => $this->billing,
            'finished_at' => $finishedAtFormated,
            'created_at'  => $createdAtFormated,
            'updated_at'  => $updatedAtFormated,
            'products'    => OrderProductResource::collection($productList),
            'invoice'     => new InvoiceResource($this->invoice)
        ];
    }
}

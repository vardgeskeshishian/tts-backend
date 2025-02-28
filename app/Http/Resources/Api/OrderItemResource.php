<?php

namespace App\Http\Resources\Api;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderItemResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        /**
         * @var OrderItem $res
         */
        $res = $this->resource;
		Log::debug('$res', [$res]);
        $content = $res->orderItemable;
		Log::debug('$content', [$content]);
		
        $class = explode('\\', $res->item_type);
		Log::debug('$class', [$class]);

        $licenseLink = !is_null($res->license_url) ? url($res->license_url) : url('/v1/public/licenses/licenseNumber/'.$res->license_number);

        return [
            'id' => $res->id,
            'content_id' => $res->item_id,
            'content_name' => $content?->name,
            'content_slug' => $content?->slug,
            'content_author_name' => $content?->authorName(),
            'type' => end($class),
            'license_number' => $res->license_number,
            'created_at' => $res->created_at,
            'licenseLink' => $licenseLink,
            'transaction_id' => $res->order?->transaction_id,
            'invoice_number' => $res->order?->invoice_number,
        ];
    }
}

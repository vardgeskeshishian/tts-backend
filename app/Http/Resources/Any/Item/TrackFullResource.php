<?php


namespace App\Http\Resources\Any\Item;

use App\Models\Track;
use App\Models\TrackPrice;
use App\Services\SearchService;
use CacheServiceFacade;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     * @throws Exception
     */
    public function toArray($request)
    {
        /**
         * @var $res Track
         */
        $res = $this->resource;

        /**
         * @var $service SearchService
         */
        $service = resolve(SearchService::class);

        $licenses = CacheServiceFacade::getFreeLicenses();

        $prices = [];

        foreach ($licenses as $licens) {
            $isLocalPrice = false;
            foreach ($res->prices as $itemPrice) {
                if ($itemPrice->license_id === $licens->id) {
                    $prices[] = $itemPrice;
                    $isLocalPrice = true;
                }
            }

            if ($isLocalPrice) {
                continue;
            }

            if ($licens->standard->discount > 0) {
                $price = new TrackPrice();
                $price->track_id = $res->id;
                $price->license_id = $licens->id;
                $price->price = $licens->info->discount;

                $prices[] = $price;
            }
        }

        return [
            [
                'id' => $res->id,
                'name' => $res->name,
                'author_name' => optional($res->author)->name ?? "",
                'description' => $res->description,
                'images' => $res->getImages(),
                'tags' => $res->getAllTags(),
                'audio' => $res->getAudioListWithFullWaveForm('wav'),
                'preview' => $res->getAudioListWithFullWaveForm('mp3'),
                'temp' => $res->tempo,
                'collections' => $res->collections,
                'duration' => optional($res->audio->where('preview_name', 'full')->first())->duration,
                'is_favorite' => $res->isFavored(),
                'prices' => $prices,
                'extra' => [
                    'premium' => $res->premium,
                    'has_content_id' => $res->has_content_id,
                ],
            ],
            $this->merge($service->similar($res))
        ];
    }
}

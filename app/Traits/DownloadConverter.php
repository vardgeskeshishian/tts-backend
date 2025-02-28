<?php

namespace App\Traits;

trait DownloadConverter
{
    public function getDownloadArrayAttribute()
    {
        return [];
//        cache()->remember('download_array_' . $this->getMorphClass() . $this->id, '1440', function() {
//            $basic        = $this instanceof OrderItem;
//            $track        = $this->track_sculpt;
//            $license_name = $this->license_sculpt->type;
//
//            if ( !$track ) return [];
//
//            return [
//                'basic'      => $basic,
//                'type'       => $license_name,
//                'slug'       => $track->slug,
//                'author'     => $track->author_name,
//                'name'       => $track->name,
//                'preview'    => $track->preview,
//                'created_at' => (string) $this->created_at,
//                'price'      => $this->price ?? 0,
//                'id'         => $basic ? $track->id : $this->id,
//                'hash'       => $track->hash,
//            ];
//        });
    }
}

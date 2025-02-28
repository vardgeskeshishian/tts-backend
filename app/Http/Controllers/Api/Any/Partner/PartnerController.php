<?php

namespace App\Http\Controllers\Api\Any\Partner;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Models\Partner\Partner;
use App\Models\Partner\PartnerLinks;
use App\Http\Controllers\Api\ApiController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PartnerController extends ApiController
{
    public function checkHash(): JsonResponse
    {
        $siteLink = request()->get('site_link');
        $hash = request()->get('hash');
        $hash = str_replace(['?', '&', 'ref='], '', $hash);

        $link = PartnerLinks::whereHash($hash)->whereSiteLink("https://taketones.com" . $siteLink)->first();

        if ($link) {
            return $this->success([
                'headers' => [
                    'partner-link' => $link->id,
                ],
            ]);
        }

        // if links are not empty - meaning, there is links for this partner, but not this particular one
        // create link for partner, and add headers
        $links = PartnerLinks::whereHash($hash)->get();

        if ($links->isNotEmpty()) {
            $link = PartnerLinks::create([
                'partner_id' => $links->first()->partner_id,
                'site_link' => "https://taketones.com" . $siteLink,
                'hash' => $hash,
                'name' => $siteLink,
            ]);

            return $this->success([
                'headers' => [
                    'partner-link' => $link->id,
                ],
                'req' => request()->all(),
            ]);
        } else {
            // if no links for partner is found - meaning partner have not created any links at all

            $partner = Partner::with('user')->get()->filter(function (Partner $partner) use ($hash) {
                $slug = Str::slug($partner->user->name);

                return $slug === $hash;
            })->first();

            if (!$partner) {
                return $this->success();
            }

            $link = PartnerLinks::create([
                'partner_id' => $partner->id,
                'site_link' => "https://taketones.com" . $siteLink,
                'hash' => $hash,
                'name' => $siteLink,
            ]);

            return $this->success([
                'headers' => [
                    'partner-link' => $link->id,
                ],
            ]);
        }
    }
}

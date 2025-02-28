<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Partner\Partner;
use App\Models\Partner\PartnerLinks;

class ReferralLocation
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $location = request()->header('Referral-Location');
        if (!$location) {
            return $next($request);
        }

        
        $location = json_decode($location);
        $siteLink = rtrim($location->pathname, '/');
        $search = str_replace('?ref', '&ref', $location->search);
        [$rest, $ref] = array_pad(explode('ref=', $search), 2, null);

        if (!$ref) {
            return $next($request);
        }

        $rest = rtrim($rest, '&');
        $siteLink .= $rest;

        $link = PartnerLinks::withTrashed()
            ->whereHash($ref)
            ->whereSiteLink("https://taketones.com" . $siteLink)
            ->first();

        if ($link) {
            return $next($request);
        }

        // if links are not empty - meaning, there is links for this partner, but not this particular one
        // create link for partner, and add headers
        $links = PartnerLinks::whereHash($ref)->get();

        if ($links->isNotEmpty()) {
            PartnerLinks::create([
                'partner_id' => $links->first()->partner_id,
                'site_link' => "https://taketones.com" . $siteLink,
                'hash' => $ref,
                'name' => $siteLink,
            ]);
        } else {
            // if no links for partner is found - meaning partner have not created any links at all

            $partner = Partner::with('user')->get()->filter(function (Partner $partner) use ($ref) {
                $slug = Str::slug($partner->user->name);

                return $slug === $ref;
            })->first();

            if (!$partner) {
                return $next($request);
            }

            PartnerLinks::create([
                'partner_id' => $partner->id,
                'site_link' => "https://taketones.com" . $siteLink,
                'hash' => $ref,
                'name' => $siteLink,
            ]);
        }

        return $next($request);
    }
}

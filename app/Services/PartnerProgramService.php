<?php


namespace App\Services;

use App\Models\User;
use App\Models\Options;
use App\Libs\OptionsLib;
use App\Models\Partner\Partner;
use App\Models\Partner\PartnerLinks;
use App\Models\Partner\PartnerUsers;
use App\Models\Partner\PartnerEarningHistory;

class PartnerProgramService
{
    public static function checkHash()
    {
        $location = request()->header('Referral-Location');
        if (!$location) {
            return;
        }

        $location = json_decode($location);
        $siteLink = $location->pathname;
        $search = str_replace('?ref', '&ref', $location->search);
        [$rest, $ref] = explode('ref=', $search);
        $rest = rtrim($rest, '&');
        $siteLink .= $rest;

        if ($siteLink === '/') {
            $siteLink = '';
        }

        $link = PartnerLinks::whereHash($ref)->whereSiteLink("https://taketones.com" . $siteLink)->first();

        if (!$link) {
            logs('telegram-debug')->info('partner-program-service:check-hash', [
                'message' => "link for [{$siteLink}:{$ref}] not found"
            ]);

            return;
        }

        if ($link->partner->status !== Partner::STATUS_ACTIVATED) {
            return;
        }

        /**
         * @var $user User
         */
        $user = auth()->user();

        if (!$user) {
            return;
        }

        if ($user->belongsToPartner($link->partner)) {
            return;
        }

        PartnerUsers::create([
            'partner_id' => $link->partner->id,
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);
    }

    public static function writeEarnings($user, $itemPrice, $source, ?int $sourceId = null)
    {
        if (!$user) {
            return;
        }

        $partnerUser = PartnerUsers::whereUserId($user->id)->with('partner')->first();

        if (!$partnerUser || !$partnerUser->partner) {
            return;
        }

        if ($user->id === $partnerUser->user_id) {
            return;
        }

        $fillData = [
            'partner_id' => $partnerUser->partner_id,
            'user_id' => $user->id,
            'source' => $source,
        ];

        if ($itemPrice > 0) {
            $partnerAward = Options::getOptionValue(OptionsLib::OPTION_PARTNER_AWARD);
            $award = ($partnerAward / 100) * $itemPrice;

            $partnerUser->partner->increment('current_balance', $award);
            $partnerUser->partner->increment('total_balance', $award);

            $fillData['award'] = $award;
        }

        if ($sourceId) {
            $fillData['source_id'] = $sourceId;
        }

        $fillData['award'] = $fillData['award'] ?? 0;

        PartnerEarningHistory::create($fillData);
    }

    public static function withdrawEarnings($user, $itemPrice, $source, ?int $sourceId = null)
    {
        if (!$user) {
            return;
        }

        $partnerUser = PartnerUsers::whereUserId($user->id)->with('partner')->first();

        if (!$partnerUser || !$partnerUser->partner) {
            return;
        }

        $fillData = [
            'partner_id' => $partnerUser->partner_id,
            'user_id' => $user->id,
            'source' => $source,
        ];

        if ($itemPrice > 0) {
            $partnerAward = Options::getOptionValue(OptionsLib::OPTION_PARTNER_AWARD);
            $award = ($partnerAward / 100) * $itemPrice;

            $partnerUser->partner->decrement('current_balance', $award);
            $partnerUser->partner->decrement('total_balance', $award);

            $fillData['award'] = $award * -1;
        }

        if ($sourceId) {
            $fillData['source_id'] = $sourceId;
        }

        $fillData['award'] = $fillData['award'] ?? 0;

//        PartnerEarningHistory::create($fillData);
    }
}

<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Services\Integration\YoutubeService;
use App\Services\Orfium\OAuthService;
use App\Services\Orfium\OrfiumService;
use Exception;
use Youtube;

class ReleaseTheClaimService
{
    /**
     * @var YoutubeService
     */
    private YoutubeService $youtubeService;

    public function __construct(
		public OAuthService $OAuthService,
		public OrfiumService $orfiumService,
	
	)
    {
        $this->youtubeService = new YoutubeService();
    }

    /**
     * @param string $link
     * @return array
     * @throws Exception
     */
    public function search(string $link): array
    {
        $videoId = Youtube::parseVidFromURL($link);
        $user = auth()->user();

        if ($user->releaseTheClaimVideos()->where('video_id', $videoId)->first()){
			throw new \Exception('Video already added', 400);
		}
		
		return $this->youtubeService->getVideoInfo(Youtube::parseVidFromURL($link));
		
    }

    /**
     * @param User $user
     * @param string $videoId
     * @return void
     * @throws Exception
     */
    public function add($user, string $videoId): void
    {
        $video = $this->youtubeService->getVideoInfo($videoId);
        if ($user->releaseTheClaimVideos()->where('video_id', $videoId)->first()) {
            return;
        }
		
		$response = $this->orfiumService->videoScans($videoId);
		
		$claimResponse = $this->orfiumService->getVideoScans($response['video_scan_id']);

        $claims = $claimResponse['result']['claims'];
        foreach ($claims as $claim)
        {
            $response = $this->orfiumService->createLicense(
                user : $user,
                channelId: null,
                license_type: "CREATORSINGLE",
                quota: 1,
                assetId: $claim['asset_id'],
                videoId: $claim['video_id'],
            );

            $video = $user->releaseTheClaimVideos()->create([
                'video_id' => $video['id'],
                'claim_response' => $claimResponse,
                'response' => $response,
                'is_active' => true,
            ]);

            $video->claims()->create([
                'license_code' => $response['code'],
                'is_active' => true,
            ]);

        }
		
        $user->claims++;
        $user->save();
    }

    /**
     * @param User $user
     * @param string $videoId
     * @return void
     * @throws Exception
     */
    public function remove($user, string $videoId): void
    {
        $video = $user->releaseTheClaimVideos()->where('video_id', $videoId)->first();
        if (!$video) {
            throw new Exception('Video not found', 404);
        }

        $claims = $video->claims;
        foreach ($claims as $claim)
        {
            if ($claim->is_active)
                $this->orfiumService->deactivateLicense($claim->license_code);
            $claim->delete();
        }
		
        $video->delete();
        $user->claims--;
        $user->save();
    }

    /**
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function getReleaseTheClaimList($user): array
    {
        $videos = $user->releaseTheClaimVideos;
        $response = [];
        foreach ($videos as $video) {
            $videoInfo = $this->youtubeService->getVideoInfo($video->video_id);
            $videoInfo['is_active'] = $video->is_active;
            $response[] = $videoInfo;
        }
        return $response;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function checkCreate($user): bool
    {
        $subscriptions = $user->getActiveSubscriptions();

        if ($subscriptions['business']) {
            $setting = Setting::where('key', 'bussiness_claims')->first();
            $limit = $setting->value ?? 0;
            if ($user->claims < $limit) {
                return true;
            }
        }

        if ($subscriptions['creator']) {
            $setting = Setting::where('key', 'creator_claims')->first();
            $limit = $setting->value ?? 0;
            if ($user->claims < $limit) {
                return true;
            }
        }

        $setting = Setting::where('key', 'free_claims')->first();
        $limit = $setting->value ?? 0;
        return $user->claims < $limit;
    }

}

<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Services\Integration\YoutubeService;
use App\Services\Orfium\OAuthService;
use App\Services\Orfium\OrfiumService;
use Exception;
use Illuminate\Support\Facades\Http;
use Log;

class WhitelistingService
{
    /**
     * @var YoutubeService
     */
    private YoutubeService $youtubeService;

    public function __construct(
		public OAuthService $oauthService,
		public OrfiumService $orfiumService,
	)
    {
        $this->youtubeService = new YoutubeService();
    }

    /**
     * @param string $query
     * @return array
     * @throws Exception
     */
    public function search(string $query): array
    {
        return $this->youtubeService->searchChannels($query);
    }

    /**
     * @param User $user
     * @param string $channelId
     * @return void
     * @throws Exception
     */
    public function add($user, string $channelId): void
    {
		//TODO this method is very raw, its need refactoring!!!!!
		$whiteList = $user->whitelist;
		
		Log::channel('orfium')->debug('Log: ' . __METHOD__, [
			'user'=> $user,
			'whitelist' => $whiteList,
		]);
		
        if ($whiteList && $whiteList->channels()->where('channel_id', $channelId)->first()) {
            throw new Exception('Whitelist already exists', 422);
        }

		if(empty($whiteList)){

			$response = $this->orfiumService->createLicense($user, $channelId);
			
			$newWhiteList = $user->whitelist()->create([
				'license' => $response['code'],
				'response' => $response,
			]);
			
			$newWhiteList->channels()->create([
				'channel_id' => $channelId,
			]);
			$newWhiteList->update([
				'is_active' => true,
			]);
		}else{
			if($whiteList->is_active === false){
				
				$this->orfiumService->activateLicense($whiteList->license);
				
				$whiteList->update([
					'is_active' => true,
				]);
			}
			
			$channels = $whiteList->channels()->pluck('channel_id');
			$this->orfiumService->updateLicenseChannels(
				$whiteList->license,
				[
					...$channels,
					$channelId,
				]
			);
			
			$user->whitelist->channels()->create([
				'channel_id' => $channelId,
			]);
		}
		
        $user->whitelists++;
        $user->save();
    }

    /**
     * @param User $user
     * @param string $channelId
     * @return void
     * @throws Exception
     */
    public function remove($user, string $channelId): void
    {
		$userWhiteList = $user->whitelist;
        $channels = $userWhiteList->channels()->get();
		$currentChannel = $channels->where('channel_id', $channelId)->first();
        if (!$currentChannel) {
            throw new Exception('Channel not found', 404);
        }
		
		$actualChannels = $channels->where('channel_id', '!=', $channelId);
		
		
		$this->orfiumService->updateLicenseChannels(
			$userWhiteList->license,
			$actualChannels->pluck('channel_id')->toArray(),
		);
		
		$currentChannel->delete();
        $user->whitelists--;
        $user->save();
		$user->refresh();
		
		if($user->whitelists === 0){
			$this->orfiumService->deactivateLicense($userWhiteList->license);
			$user->whitelist->update([
				'is_active' => false,
			]);
		}
    }

    /**
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function getWhitelist($user): array
    {
		$whiteList = $user->whitelist;
        $channels = $whiteList
			? $whiteList->channels()->get()
			: [];
        $response = [];
        foreach ($channels as $channel) {
            $response[] = $this->youtubeService->getChannelInfo($channel->channel_id, $channel);
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
            $setting = Setting::where('key', 'bussiness_whitelists')->first();
            $limit = $setting->value ?? 0;
            if ($user->whitelists < $limit) {
                return true;
            }
        }

        if ($subscriptions['creator']) {
            $setting = Setting::where('key', 'creator_whitelists')->first();
            $limit = $setting->value ?? 0;
            if ($user->whitelists < $limit) {
                return true;
            }
        }

        $setting = Setting::where('key', 'free_whitelists')->first();
        $limit = $setting->value ?? 0;
        return $user->whitelists < $limit;
    }
}

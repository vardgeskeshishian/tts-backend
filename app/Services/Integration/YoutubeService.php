<?php

namespace App\Services\Integration;

use Youtube;

class YoutubeService
{
    /**
     * @param string $q
     * @return array
     * @throws \Exception
     */
    public function searchChannels(string $q)
    {
        $result = [];
		
		if(empty($q)){
			return $result;
		}
		
        $channels = Youtube::searchAdvanced(['q' => $q, 'type' => 'channel']);

		if(!is_array($channels) && !is_object($channels)){
			return $result;
		}

        $user = auth()->user();
        $usersChannelIds = $user->whitelist?->channels()->pluck('channel_id')->toArray() ?? [];

        foreach ($channels as $channel) {
            if (!in_array($channel->id->channelId, $usersChannelIds))
                $result[] = $this->getChannelInfo($channel->id->channelId);
        }
        return $result;
    }

    /**
     * @param string $videoId
     * @return array
     * @throws \Exception
     */
    public function getVideoInfo(string $videoId)
    {
        try {
            $video = Youtube::getVideoInfo($videoId);
        } catch (\Exception $e) {
            throw new \Exception('Video not found', 404);
        }

        return [
            'id' => $video->id,
            'text' => $video->snippet->title,
            'img' => $video->snippet->thumbnails->medium->url ?? $video->snippet->thumbnails->default->url,
        ];
    }

    /**
     * @param string $channelId
     * @return array
     * @throws \Exception
     */
    public function getChannelInfo(string $channelId, $channel = null): array
	{
        try {
            $findChannel = Youtube::getChannelById($channelId);
        } catch (\Exception $e) {
            throw new \Exception('Channel not found', 404);
        }

        $response = [
            'id' => $findChannel->id,
            'text' => $findChannel->snippet->title,
            'img' => $findChannel->snippet->thumbnails->default->url ?? '',
			'url' => $findChannel->snippet->customUrl ?? null,
        ];
		
		if($channel){
			$response = array_merge($response, ['is_active' => $channel->is_active]);
		}
		
		return $response;
    }
}

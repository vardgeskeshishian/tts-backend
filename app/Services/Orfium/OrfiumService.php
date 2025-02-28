<?php

namespace App\Services\Orfium;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Log;

class OrfiumService
{
	private OAuthService $oauthService;
	
	private static string $orfiumOrigin;
	
	private static string $orfiumApiVersion;
	
	private static string $account = 'Taketones';
	
	public function __construct()
	{
		$this->oauthService = new OAuthService();
		self::$orfiumOrigin = config('app.env') == 'prod' ? config('orfium.api_domain') : config('orfium.sandbox_domain');
		self::$orfiumApiVersion = 'v2';
	}
	
	private function getAccessToken(): string
	{
		return $this->oauthService->getAccessToken();
	}
	
	/**
	 * @throws Exception
	 */
	public function createLicense(
		User $user,
		?string $channelId,
		$license_type = 'ADVERTISING',
		$quota = 0,
        ?string $assetId = null,
        ?string $videoId = null
	): array
	{
        $request = [
            "license_type" => $license_type,
            "quota" => $quota,
            "customer" => [
                "customer_id" => $user->id,
                "customer_name" => $user->email,
            ],
        ];

        is_null($channelId) ? : $request['channel_ids'] = [$channelId];
        is_null($assetId) ? : $request['asset_id'] = $assetId;
        is_null($videoId) ? : $request['video_id'] = $videoId;

		$response = Http::withToken($this->getAccessToken())
			->post(sprintf('%s/%s/license', self::$orfiumOrigin, self::$orfiumApiVersion), $request);
		
		Log::channel('orfium')->debug('Log: ' . __METHOD__, [
			'response' => $response->json(),
			'is_success' => $response->successful(),
			'status'=> $response->status(),
			'user' => $user,
			'channelId' => $channelId,
		]);
		
		if (!$response->successful() || $response->status() !== 201) {
			throw new Exception('Error : ' . __METHOD__, 422);
		}
		
		return $response->json();
	}
	
	/**
	 * @throws Exception
	 */
	public function activateLicense(string $licenseCode): array
	{
		$response = Http::withToken($this->getAccessToken())
			->post(sprintf('%s/%s/license/activate', self::$orfiumOrigin, self::$orfiumApiVersion), [
				"license_code" => $licenseCode,
			]);
		
		Log::channel('orfium')->debug('Log: ' . __METHOD__, [
			'response' => $response->json(),
			'licenseCode' => $licenseCode,
		]);
		
		if (!$response->successful() || $response->status() !== 200) {
			throw new Exception('Error : ' . __METHOD__, 422);
		}
		
		return $response->json();
	}
	
	/**
	 * @throws Exception
	 */
	public function updateLicenseChannels(string $licenseCode, array $channelIds): array
	{
		$response = Http::withToken($this->getAccessToken())
			->patch(sprintf('%s/%s/license/%s', self::$orfiumOrigin, self::$orfiumApiVersion, $licenseCode), [
				"channel_ids" => $channelIds,
			]);
		
		Log::channel('orfium')->debug('Log: ' . __METHOD__, [
			'response' => $response->json(),
			'licenseCode' => $licenseCode,
			'channelIds' => $channelIds,
		]);
		
		if (!$response->successful() || $response->status() !== 200) {
			throw new Exception('Error : ' . __METHOD__, 422);
		}
		
		return $response->json();
	}
	
	/**
	 * @throws Exception
	 */
	public function deactivateLicense(string $licenseCode): true
	{
		Log::channel('orfium')->info('Starting deactivate ' . $licenseCode . ' orfium license');
		$response = Http::withToken($this->getAccessToken())
			->post(sprintf('%s/%s/license/deactivate', self::$orfiumOrigin, self::$orfiumApiVersion), [
				"license_code" => $licenseCode,
			]);
		
		Log::channel('orfium')->debug('Log: ' . __METHOD__, [$response->json()]);
		
		if (!$response->successful() || $response->status() !== 200) {
			throw new Exception('Error : ' . __METHOD__, 422);
		}
		
		return true;
	}
	
	/**
	 * @throws Exception
	 */
	public function videoScans(string $videoId): array
	{
		$response = Http::withToken($this->getAccessToken())
			->withHeaders([
				'account' => self::$account,
			])
			->post(sprintf('%s/%s/video-scans', self::$orfiumOrigin, self::$orfiumApiVersion), [
				"video_id" => $videoId,
			]);
		
		Log::channel('orfium')->debug('Log: ' . __METHOD__, [
			'response' => $response->json(),
			'videoId' => $videoId,
		]);
		
		if (!$response->successful() || $response->status() !== 200) {
			throw new Exception('Error : ' . __METHOD__, 422);
		}
		
		if(empty($response['video_scan_id'])){
			throw new Exception('Orfium error: Cant take video-scans');
		}
		
		return $response->json();
	}
	
	
	/**
	 * @throws Exception
	 */
	public function getVideoScans(string $videoScanId): array
	{
        $isStatusInProgress = true;
        while ($isStatusInProgress) {
            $response = Http::withToken($this->getAccessToken())
                ->withHeaders([
                    'account' => self::$account,
                ])
                ->get(sprintf('%s/%s/video-scans/%s', self::$orfiumOrigin, self::$orfiumApiVersion, $videoScanId));
            $isStatusInProgress = $response['status'] === 'IN_PROGRESS';
        }
		
		Log::channel('orfium')->debug('Log: ' . __METHOD__, [
			'response' => $response->json(),
			'videoScanId' => $videoScanId,
		]);
		
		if (!$response->successful() || $response->status() !== 200) {
			throw new Exception('Error : ' . __METHOD__, 422);
		}
		
		if(empty($response)){
			throw new Exception('The video does not contain any claims.');
		}
		
		if(empty($response['result']['claims'])){
			throw new Exception('No claims were found on this video.');
		}
		
		return $response->json();
	}
	
}

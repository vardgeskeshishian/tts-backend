<?php

namespace App\Services\Orfium;

use Illuminate\Support\Facades\Http;

class OAuthService
{
	protected string $clientId;
	protected string $clientSecret;
	protected string $tokenUrl;
	
	public function __construct()
	{
		$this->clientId = config('orfium.client_id');
		$this->clientSecret = config('orfium.client_secret');
		$this->tokenUrl = config('orfium.auth_url');
	}
	
	/**
	 * Get OAuth2 access token
	 *
	 * @return string|null
	 */
	public function getAccessToken()
	{
		$accessToken = cache('orfium_access_token');
		
		if (!$accessToken) {
			$response = Http::asForm()->post($this->tokenUrl, [
				'grant_type'    => 'client_credentials',
				'client_id'     => $this->clientId,
				'client_secret' => $this->clientSecret,
			]);
			
			if ($response->successful()) {
				$accessToken = $response->json()['access_token'];
				
				cache(['orfium_access_token' => $accessToken], now()->addHour());
			}
		}
		
		return $accessToken;
	}
}

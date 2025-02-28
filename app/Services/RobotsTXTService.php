<?php

namespace App\Services;

class RobotsTXTService
{
	public function getDisallowRules(): array
	{
		$robotsTxt = file_get_contents(base_path()."/public_html/robots.txt");
		
		if ($robotsTxt === false) {
			return []; // Return an empty array if there was an error
		}
		
		$disallowRules = [];
		$lines = explode("\n", $robotsTxt);
		
		foreach ($lines as $line) {
			$line = trim($line);
			
			if (str_starts_with($line, 'Disallow:')) {
				$disallowPath = trim(substr($line, strlen('Disallow:')));
				$disallowRules[] = $disallowPath;
			}
		}
		
		return $disallowRules;
	}
}

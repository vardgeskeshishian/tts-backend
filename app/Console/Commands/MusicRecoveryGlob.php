<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class MusicRecoveryGlob extends Command
{
	protected $signature = 'recovery:music:glob';
	protected $description = 'Recover music files by checking for available suffixes and moving them to corresponding folders.';
	
	public function handle(): void
	{
		$startTime = microtime(true);
		$this->info('Start recovery...');
		
		$musicDir = '/mnt/storage_new/music_new';
		$newOneDir = '/mnt/storage_new/new-one';
		
		//$musicDir = public_path('music');
		//$newOneDir = public_path('new-one');
		
		$suffixes = ['-15s', '-30s', '-60s', '-loop1', '-loop2', '-loop3', '-loop4', '-loop5', '-loop6'];
		
		$folders = Cache::remember('music_directories', 4320, function () use ($musicDir) {
			return File::directories($musicDir);
		});
		
		foreach ($folders as $folder) {
			$folderName = basename($folder);
			$this->info("Processing folder: {$folderName}");
			
			$normalizedFolderName = Str::slug($folderName, '-');
			
			foreach ($suffixes as $suffix) {
				$targetFileName = "{$normalizedFolderName}{$suffix}.mp3";
				$sourceFilePath = "{$newOneDir}/{$targetFileName}";
				
				if (File::exists($sourceFilePath)) {
					$prettySuffix = Str::ucfirst(Str::remove('-', $suffix));
					$newFileName = "{$folderName} {$prettySuffix}.mp3";
					
					File::move($sourceFilePath, "{$folder}/{$newFileName}");
					$this->info("Moved: {$sourceFilePath} to {$folder}/{$newFileName}");
				} else {
					$this->info("File does not exist: {$sourceFilePath}");
				}
			}
			
			$this->createZipArchive($folder, $musicDir, $folderName);
		}
		
		$endTime = microtime(true);
		$executionTime = $endTime - $startTime;
		$this->info("Process completed. Execution time: " . round($executionTime, 2) . " seconds.");
	}
	
	private function createZipArchive($folder, $musicDir, $folderName): void
	{
		$zip = new ZipArchive();
		$zipFileName = "{$musicDir}/{$folderName}/{$folderName}.zip";
		
		if (File::exists($zipFileName)) {
			$this->info("Zip file already exists: {$zipFileName}. Skipping zipping for this folder.");
			return;
		}
		
		if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
			$filesToZip = File::files($folder);
			foreach ($filesToZip as $file) {
				$zip->addFile($file->getPathname(), $file->getFilename());
			}
			$zip->close();
			$this->info("Создан архив: {$zipFileName}");
		} else {
			$this->error("Не удалось создать архив для {$folderName}");
		}
	}
}

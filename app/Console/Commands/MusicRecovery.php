<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class MusicRecovery extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'recovery:music';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle(): void
	{
		$startTime = microtime(true); // Start time
		$this->info('Start recovery...');
//		$musicDir = '/mnt/storage/music';
//		$newOneDir = '/mnt/storage/new-one';
		
		$musicDir = public_path('music');
		$newOneDir = public_path('new-one');
		
		$folders = File::directories($musicDir);
		$tracks = File::files($newOneDir);
		
		foreach (array_slice($folders, 0, 20) as $folder) {
			$folderName = basename($folder);
			$this->info("Обработка папки: {$folderName}");
			
			$normalizedFolderName = strtolower(str_replace(' ', '-', $folderName));
			
			$matchedTracks = [];
			
			foreach ($tracks as $track) {
				$trackName = $this->removeFirstDotIfTwo(strtolower($track->getFilename()));
				$this->info('Founded track name: ' . $trackName);
				// Проверяем, совпадает ли имя трека с нормализованным именем папки
				if (preg_match("/^$normalizedFolderName(-\d+s|-full|-loop\d+)?\.mp3$/i", $trackName)) {
					$this->info('Founded track: ' . $track);
					$matchedTracks[] = $track;
				}
			}
			
			
//			foreach ($matchedTracks as $track) {
//				$suffix = pathinfo($track->getFilename(), PATHINFO_FILENAME);
//				//$newName = "{$folderName}_{$suffix}." . $track->getExtension();
//
//				$newName = "{$suffix}." . $track->getExtension();
//				File::copy($track->getPathname(), "{$folder}/{$newName}");
//			}
			
			
			foreach ($matchedTracks as $track) {
				if (preg_match("/^(.*?)(-\d+s|-full|-loop\d+)?\.mp3$/i", $track->getFilename(), $matches)) {
					$suffix = Str::ucfirst(trim($matches[2] ?? '', '-'));
					$newMp3Name = "{$folderName} {$suffix}.mp3";
					$newWavName = "{$folderName} {$suffix}.wav";
					
					File::move($track->getPathname(), "{$folder}/{$newMp3Name}");
					
					if (!strpos($track->getFilename(), 'full')) {
						$this->convertMp3ToWav("{$folder}/{$newMp3Name}", "{$folder}/{$newWavName}");
					}else{
						$this->info("Skip: ". $track->getFilename());
					}
				}
			}
			
			$zip = new ZipArchive();
			$zipFileName = "{$musicDir}/{$folderName}/{$folderName}.zip";

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
		
		$endTime = microtime(true);
		$executionTime = $endTime - $startTime;
		$this->info("Процесс завершен. Время выполнения: " . round($executionTime, 2) . " секунд.");
	}
	
	private function convertMp3ToWav($inputFile, $outputFile): void
	{
		$command = "ffmpeg -i " . escapeshellarg($inputFile) . " " . escapeshellarg($outputFile);
		exec($command, $output, $returnVar);
		
		if ($returnVar !== 0) {
			$this->error("Ошибка при конвертации {$inputFile} в WAV.");
		} else {
			$this->info("Конвертирован {$inputFile} в {$outputFile}.");
		}
	}
	
	public function removeFirstDotIfTwo($string): string
	{
		if (substr_count($string, '.') >= 2) {
			return Str::replaceFirst('.', '', $string);
		}
		return $string;
	}
}

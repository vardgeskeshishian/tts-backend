<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class MusicRecoveryBetta extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'recovery:music:betta';
	
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
		$startTime = microtime(true);
		$this->info('Start recovery...');
		
		//$musicDir = '/mnt/storage_new/music';
		//$newOneDir = '/mnt/storage_new/new-one';
		
		$musicDir = public_path('music');
		$newOneDir = public_path('new-one');
		
		$folders = File::directories($musicDir);
		$tracks = File::files($newOneDir);
		foreach ($folders as $folder) {
			$folderName = basename($folder);
			$this->info("Обработка папки: {$folderName}");
			
			$normalizedFolderName = strtolower(str_replace(' ', '', $folderName));
			$this->info('Count track: ' . count($tracks));
			foreach ($tracks as $key => &$track) {
				if (preg_match("/^(.*?)(-\d+s|-full|-loop\d+)?\.mp3$/i", $track->getFilename(), $matches)) {
					if(Str::remove('-', $matches[1]) === $normalizedFolderName){
						$this->info('Founded track: ' . $track);
						$suffix = Str::ucfirst(trim($matches[2] ?? '', '-'));
						$newWavName = "{$folderName} {$suffix}.mp3";
						
						File::move($track->getPathname(), "{$folder}/{$newWavName}");
						unset($tracks[$key]);
					}
				}
			}
			
			$zip = new ZipArchive();
			$zipFileName = "{$musicDir}/{$folderName}/{$folderName}.zip";
			
			if (File::exists($zipFileName)) {
				$this->info("Zip file already exists: {$zipFileName}. Skipping zipping for this folder.");
				continue; // Skip to the next folder
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
	
	public function removeSymbols($string): string
	{
		$removed = preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($string, PATHINFO_FILENAME));
		$this->info('removeFirstDotIfTwo: ' . $removed);
		return $removed;
	}
}

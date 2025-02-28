<?php

namespace App\Console\Commands;

use App\Models\UserDownloads;
use App\Models\Order;
use App\Services\LicenseNumberService;
use Illuminate\Console\Command;

class GenerateLicenseNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license-number:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate license numbers where they are empty';
    protected $licenseNumberService;


    /**
     * Create a new command instance.
     *
     * @param LicenseNumberService $licenseNumberService
     */
    public function __construct(LicenseNumberService $licenseNumberService)
    {
        parent::__construct();

        $this->licenseNumberService = $licenseNumberService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $total = UserDownloads::count() + Order::withCount('items')->get()->sum('items_count');

        $bar = $this->output->createProgressBar($total);
        $bar->display();

        UserDownloads::chunk(200, function ($downloads) use ($bar) {
            foreach ($downloads as $download) {
                $license = $download->license_sculpt;

                $bar->advance();

                if ($download->license_number) {
                    continue;
                }

                $download->license_number = $this->licenseNumberService->generate($license);
                $download->save();
            }
        });

        Order::chunk(200, function ($orders) use ($bar) {
            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    $license = $item->license_sculpt;

                    $bar->advance();

                    if ($item->license_number) {
                        continue;
                    }

                    $item->license_number = $this->licenseNumberService->generate($license);
                    $item->save();
                }
            }
        });
    }
}

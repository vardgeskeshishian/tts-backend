<?php

namespace App\Excel\Importers\FirstSheet;

use App\Models\VideoEffects\VideoEffectCategory;
use App\Services\VideoEffectsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class MissingCategoriesFirstSheet implements WithHeadingRow, WithProgressBar, ToCollection
{
    use Importable;

    private VideoEffectsService $effectsService;

    /**
     * @param VideoEffectsService $effectsService
     */
    public function __construct(
        VideoEffectsService $effectsService,
    ) {
        $this->effectsService = $effectsService;
    }

    public function collection(Collection $rows)
    {
        $collected = [];

        foreach ($rows as $row) {
            $collected = array_merge($collected, explode(",", $row['categories']));
        }

        $collected = array_unique($collected);

        $newlyCreated = 0;

        foreach ($collected as $item) {
            $item = trim($item);
            $category = VideoEffectCategory::firstOrCreate([
                'slug' => $item,
            ], [
                'name' => Str::title($item),
                'price_standard' => 9,
                'price_extended' => 80,
            ]);

            if (!$category->wasRecentlyCreated) {
                continue;
            }

            $newlyCreated++;
        }

        $this->output->info("was created: {$newlyCreated}");
    }
}

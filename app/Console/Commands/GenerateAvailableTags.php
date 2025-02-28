<?php

namespace App\Console\Commands;

use App\Models\Tags\Tag;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectResolution;
use App\Models\VideoEffects\VideoEffectTag;
use App\Models\VideoEffects\VideoEffectVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateAvailableTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:available-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates available tags/categories/genres and puts them to json for later validation of url';

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
     * @return int
     */
    public function handle()
    {
        $tags = VideoEffectApplication::pluck('name')
            ->merge(VideoEffectCategory::pluck('name'))
            ->merge(VideoEffectResolution::pluck('name'))
            ->merge(VideoEffectResolution::pluck('full'))
            ->merge(VideoEffectResolution::pluck('short'))
            ->merge(VideoEffectVersion::pluck('name'))
            ->merge(VideoEffectTag::pluck('name'))
            ->merge(Tag::pluck('name'));

        $newTags = clone $tags;

        $tags->lazy()->map(function ($i) use ($newTags) {
            $newTags->push(Str::slug($i));
        });

        $newTags = $newTags->unique();

        file_put_contents('ultimate.json', json_encode($newTags));

        return 0;
    }
}

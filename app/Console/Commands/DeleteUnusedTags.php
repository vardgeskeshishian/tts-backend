<?php

namespace App\Console\Commands;

use App\Models\Tags\Tag;
use App\Models\Tags\Tagging;
use Illuminate\Console\Command;

class DeleteUnusedTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tags:delete-unused';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unused tags';

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
    public function handle()
    {
        $tagsCounter = [];

        Tag::chunk(1000, function ($tags) use (&$tagsCounter) {
            foreach ($tags as $tag) {
                $markedForDelete = Tagging::where('tag_type', Tag::class)
                        ->where('tag_id', $tag['id'])
                        ->count() === 0;

                if ($markedForDelete) {
                    $tagsCounter[] = $tag['id'];
                }
            }
        });

        Tag::whereIn('id', $tagsCounter)->delete();
    }
}

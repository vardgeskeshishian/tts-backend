<?php

namespace App\Console\Commands\VFX;

use App\Excel\Importers\FirstSheet\AssociatedMusicFirstSheet;

class InitialParseAssociatedMusic extends InitialParse
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vfx:parser:initial:associated-music
    {--path= : path to xsl file used for parsing video-effects}
    {--limit= : limit number of effects that gonna be parsed}
    {--offset= : set an offset, if one wants to skip already filled vfx}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse excel file and fill associated music';

    protected string $templateClassName = AssociatedMusicFirstSheet::class;
}

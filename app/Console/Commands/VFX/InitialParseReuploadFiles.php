<?php

namespace App\Console\Commands\VFX;

use App\Excel\Importers\FirstSheet\ReuploadFilesFirstSheet;
use App\Excel\Importers\ReuploadFilesVideoEffectImport;

class InitialParseReuploadFiles extends InitialParse
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vfx:parser:initial:reupload-files
    {--path= : path to xsl file used for parsing video-effects}
    {--limit= : limit number of effects that gonna be parsed}
    {--offset= : set an offset, if one wants to skip already filled vfx}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse excel file and reupload image/video/zip of video-effects';

    protected string $templateClassName = ReuploadFilesFirstSheet::class;

}

<?php

namespace App\Console\Commands\VFX;

use App\DTO\InitialParseCommandDTO;
use App\Excel\Importers\VideoEffectImporter;
use Illuminate\Console\Command;

abstract class InitialParse extends Command
{
    protected string $templateClassName;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getOptions()
    {
        $pathToExcelFile = $this->option('path');

        if (empty($pathToExcelFile)) {
            $this->error("No path was given");
            return 1;
        }

        if (!file_exists($pathToExcelFile)) {
            $this->error("File do not exists");
            return 1;
        }

        $limit = $this->option('limit');
        if (empty($limit)) {
            $limit = -1;
        }

        $offset = $this->option('offset');
        if (empty($offset)) {
            $offset = 0;
        }

        return new InitialParseCommandDTO($pathToExcelFile, $limit, $offset);
    }

    public function handle()
    {
        $dto = $this->getOptions();

        $this->info("Starting import");
        (new VideoEffectImporter())
            ->setLimit($dto->limit)
            ->setOffset($dto->offset)
            ->setTemplateClassName($this->templateClassName)
            ->withOutput($this->output)
            ->import($dto->path);
        $this->info("Import done");

        return 0;
    }
}

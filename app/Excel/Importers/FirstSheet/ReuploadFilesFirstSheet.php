<?php

namespace App\Excel\Importers\FirstSheet;

use App\Services\VideoEffectsService;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Row;

class ReuploadFilesFirstSheet implements WithHeadingRow, WithProgressBar, WithCalculatedFormulas, OnEachRow
{
    use Importable;

    private VideoEffectsService $effectsService;
    private int $limit;
    private int $offset;

    /**
     * @param VideoEffectsService $effectsService
     * @param int $limit - limit the numbers of rows
     * @param int $offset
     */
    public function __construct(
        VideoEffectsService $effectsService,
        int                 $limit,
        int                 $offset
    )
    {
        $this->effectsService = $effectsService;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex() - 1;
        $rowData = $row->toCollection(null, true);

        if ($this->limit > 0 && $rowIndex > $this->limit || $this->offset > 0 && $rowIndex <= $this->offset) {
            $this->output->info([
                "skipping due to limit or offset",
                "the limit is $this->limit",
                "the offset is $this->offset",
                "current index is $rowIndex",
            ]);

            return null;
        }

        return $this->effectsService->updateFilesFromExcel($rowData);
    }
}

<?php

namespace App\Excel\Importers\FirstSheet;

use App\Services\VideoEffectsService;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Row;

class FillCorruptedFieldsFirstSheet implements WithHeadingRow, WithProgressBar, WithCalculatedFormulas, OnEachRow
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
    ) {
        $this->effectsService = $effectsService;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex() - 1;
        $rowData = $row->toCollection(null, true);

        if ($this->limit > 0 && $rowIndex > $this->limit || $this->offset > 0 && $rowIndex <= $this->offset) {
            return null;
        }

        return $this->effectsService->updateVideoEffectFields($rowData, [
            'category_ids' => 'categories',
            'resolution_ids' => 'resolution',
        ]);
    }
}

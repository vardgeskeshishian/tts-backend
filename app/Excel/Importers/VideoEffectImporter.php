<?php

namespace App\Excel\Importers;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VideoEffectImporter implements WithMultipleSheets
{
    use Importable;

    protected int $offset;
    protected int $limit;
    protected string $templateClassName;

    public function setLimit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    public function setTemplateClassName(string $templateClassName)
    {
        $this->templateClassName = $templateClassName;

        return $this;
    }

    public function sheets(): array
    {
        return [
            'Templates' => resolve($this->templateClassName, [
                'limit' => $this->limit,
                'offset' => $this->offset,
            ])->withOutput($this->output),
        ];
    }
}

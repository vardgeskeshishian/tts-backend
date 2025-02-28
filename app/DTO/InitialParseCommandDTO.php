<?php

namespace App\DTO;

class InitialParseCommandDTO
{
    public string $path;
    public int $limit;
    public int $offset;

    public function __construct($path, $limit, $offset)
    {
        $this->path = $path;
        $this->limit = $limit;
        $this->offset = $offset;
    }
}

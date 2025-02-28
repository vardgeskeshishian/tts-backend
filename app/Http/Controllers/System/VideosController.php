<?php

namespace App\Http\Controllers\System;

use App\Models\Video;
use App\Services\VideosService;
use App\Http\Controllers\Api\ApiController;

class VideosController extends ApiController
{
    /**
     * @var VideosService
     */
    protected $videosService;

    public function __construct(
        VideosService $videosService
    ) {
        parent::__construct();
        $this->videosService = $videosService;
    }

    public function unlink($id)
    {
        $this->videosService->unlink(Video::find($id));
        return redirect()->back();
    }
}

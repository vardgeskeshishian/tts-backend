<?php


namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class PDFMergerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'PDFMerger';
    }
}

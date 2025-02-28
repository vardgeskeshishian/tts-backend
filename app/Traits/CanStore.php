<?php

namespace App\Traits;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Throwable;

trait CanStore
{
    protected $disk = 'public';
    protected $namespace = 'common';

    public function setCloudNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return Filesystem
     */
    protected function getStorage()
    {
        return Storage::disk($this->disk);
    }

    protected function storeInCloud($path, $fileName, $file)
    {
        $res = null;
        try {
            $res = $this->getCloudStorage()
                ->putFileAs($path, $file, $fileName, ['visibility' => 'public']);
        } catch (Throwable $e) {
            logs('telegram-debug')->error($e->getMessage(), [$path, $fileName]);
        }

        $this->namespace = 'common';

        return $res;
    }

    protected function getCloudStorage()
    {
        return Storage::disk("spaces.$this->namespace");
    }
}

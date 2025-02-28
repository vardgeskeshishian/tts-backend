<?php

namespace App\Services;

use App\Traits\CanStore;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FilesService
{
    use CanStore;

    const VideoEffectThumbnailHeight = 243;
    const VideoEffectThumbnailWidth = 423;
    private string $extension;
    private string $type;
    private UploadedFile $file;
    private $thumbnail = null;

    public function setConfig(string $simpleType, UploadedFile $file, string $extension = "")
    {
        $this->type = $simpleType;
        $this->file = $file;
        $this->extension = $extension;

        return $this;
    }

    public function simpleUpload()
    {
        return '/storage/' . $this
                ->getStorage()
                ->putFileAs($this->getPath(), $this->file, $this->getFileName());
    }

    public function cloudUpload()
    {
        $fileName = $this->getFileName();

        $this->storeInCloud("files/$this->type", $fileName, $this->file);

        return "files/$this->type/$fileName";
    }

    public function uploadThumbnail()
    {
        $fileName = $this->getFileName();

        $image = Image::make($this->file);
        $thumbnail = $image->resize(self::VideoEffectThumbnailWidth, self::VideoEffectThumbnailHeight);
        @mkdir("/var/www/temp/$this->type", 0777, true);
        $tmp = $thumbnail->save("/var/www/temp/$this->type/$fileName", 70, 'webp');

        $this->storeInCloud("files/thumbnails/$this->type", $fileName, new UploadedFile($tmp->basePath(), $fileName));

        unlink($tmp->basePath());

        return "files/thumbnails/$this->type/$fileName";
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    protected function getPath()
    {
        $md5id = md5($this->type);
        return "/files/$md5id";
    }

    protected function getFileName()
    {
        $extension = $this->extension;
        $extension = $extension === "" ? $this->file->guessClientExtension() : $extension;
        return sprintf("%s.%s", md5($this->file->getClientOriginalName()), $extension);
    }
}

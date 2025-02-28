<?php

namespace App\DTO\VFX;

use Illuminate\Http\UploadedFile;

class VideoEffectExcelDTO
{
    public string $effectName;
    public string $effectNameId;
    public string $productId;
    public UploadedFile $previewImage;
    public UploadedFile $previewVideo;
    public UploadedFile $zip;

    public function setEffectName(string $effectNameId, string $effectName)
    {
        $this->effectNameId = $effectNameId;
        $this->effectName = $effectName;
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    public function setFiles(UploadedFile $previewImage, UploadedFile $previewVideo, UploadedFile $zip)
    {
        $this->previewImage = $previewImage;
        $this->previewVideo = $previewVideo;
        $this->zip = $zip;
    }
}

<?php

namespace App\Helpers\VFX;

use App\DTO\VFX\VideoEffectExcelDTO;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class VideoEffectExcelHelper
{
    private Collection $rowData;
    private VideoEffectExcelDTO $dto;

    public function __construct()
    {
        $this->dto = new VideoEffectExcelDTO();
    }

    public function setData(Collection $rowData)
    {
        $this->rowData = $rowData;

        return $this;
    }

    public function parseExcelRowData(): VideoEffectExcelDTO
    {
        $this->getEffectName();
        if ($this->dto->effectNameId === '09096' || $this->dto->effectNameId === '15016') {
            return $this->dto;
        }

        $this->getEffectFiles();

        return $this->dto;
    }

    private function getEffectName()
    {
        $productId = $this->rowData['product_id_name'];

        $effectNameProperties = explode("]", $productId);
        $effectNameId = trim($effectNameProperties[0]);
        $effectName = trim($effectNameProperties[1]);

        $this->dto->setProductId($productId);
        $this->dto->setEffectName($effectNameId, $effectName);
    }

    private function getEffectFiles(): void
    {
        $this->getEffectName();

        $productId = $this->dto->productId;
        $effectName = $this->dto->effectName;

        $VFXFolder = "/mnt/volume_sfo2_02/VFX";
        $productFolder = "$VFXFolder/$productId";

        if (!file_exists($productFolder)) {
            throw new FileNotFoundException("folder $productFolder not found");
        }

        $previewImage = new UploadedFile("$productFolder/preview.jpg", "preview.jpg");
        $previewVideo = new UploadedFile("$productFolder/preview.mp4", "preview.mp4");

        if ($zip = glob("$productFolder/*.zip")) {
            $zip = new UploadedFile($zip[0], $effectName . ".zip");
        }

        dump($productFolder);
        $this->dto->setFiles($previewImage, $previewVideo, $zip);
    }
}

<?php

namespace App\Services;

use Cache;
use Exception;
use App\Traits\CanStore;
use Illuminate\Support\Str;
use App\Repositories\ImagesRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImagesService
{
    use CanStore;

    /**
     * @var FilesystemAdapter
     */
    protected $storage;
    /**
     * @var ImagesRepository
     */
    private $imagesRepository;

    public function __construct(ImagesRepository $imagesRepository)
    {
        $this->storage = $this->getStorage();
        $this->setCloudNamespace("common");
        $this->imagesRepository = $imagesRepository;
    }

    /**
     * @param string $type
     * @param $object
     */
    public function make(string $type, $object)
    {
    }

    /**
     * @param Model $model
     * @param array[UploadedFile] $images
     *
     * @param string $extension
     *
     * @return array
     * @throws Exception
     */
    public function upload(Model $model, array $images, string $extension = "")
    {
        $path = "images";

        $links = [];

        /**
         * @var $image UploadedFile
         */
        foreach ($images as $key => $image) {
            $imageName = sprintf(
                "%s.%s",
                md5($image->getClientOriginalName()),
                $extension === "" ? $image->guessClientExtension() : $extension
            );

            $this->storeInCloud("/f/{$path}", $imageName, $image);
            $image->move("/home/admin/web/static.taketones.com/public_html/f/{$path}", $imageName);

            $links[$key] = "/f/{$path}/{$imageName}";

            $this->imagesRepository->insertOneForModel($model, $links[$key], $key);
        }

        Cache::forget(Str::slug($model->getMorphClass()) . ":images:" . $model->id);

        return $links;
    }

    public function simpleUpload(string $simpleType, UploadedFile $file, string $key, string $extension = "")
    {
        $path = "images";

        $imageName = sprintf(
            "%s.%s",
            md5($file->getClientOriginalName()),
            $extension === "" ? $file->guessClientExtension() : $extension
        );

        $this->storeInCloud("/f/{$path}", $imageName, $file);
        $file->move("/home/admin/web/static.taketones.com/public_html/f/{$path}", $imageName);

        $link = "/f/{$path}/{$imageName}";
        $this->imagesRepository->insertSimple($simpleType, $link, $key);

        return $link;
    }
}

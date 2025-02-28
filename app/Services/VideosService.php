<?php

namespace App\Services;

use Exception;
use App\Models\Video;
use App\Traits\CanStore;
use App\Repositories\VideosRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideosService
{
    use CanStore;

    /**
     * @var FilesystemAdapter
     */
    protected $storage;
    /**
     * @var VideosRepository
     */
    private VideosRepository $videosRepository;

    public function __construct(VideosRepository $videosRepository)
    {
        $this->storage = $this->getStorage();
        $this->videosRepository = $videosRepository;
    }

    /**
     * @param Model $model
     * @param array $videos [UploadedFile]
     *
     * @param string $extension
     *
     * @return array
     * @throws Exception
     */
    public function upload(Model $model, array $videos, string $extension = "")
    {
        $md5id = md5($model->id);
        $path = "/videos/$md5id";

        $links = [];

        /**
         * @var $video UploadedFile
         */
        foreach ($videos as $key => $video) {
            $videoName = sprintf(
                "%s.%s",
                md5($video->getClientOriginalName()),
                $extension === "" ? $video->guessClientExtension() : $extension
            );

            $this->storeInCloud($path, $videoName, $video);
            $links[$key] = '/storage/' . $this->storage->putFileAs($path, $video, $videoName);

            $this->videosRepository->insertOneForModel($model, $links[$key]);
        }

        return $links;
    }

    public function simpleUpload(string $simpleType, UploadedFile $file, string $extension = "")
    {
        $md5id = md5($simpleType);
        $path = "/videos/{$md5id}";

        $imageName = sprintf(
            "%s.%s",
            md5($file->getClientOriginalName()),
            $extension === "" ? $file->guessClientExtension() : $extension
        );

        $this->storeInCloud($path, $imageName, $file);
        $link = '/storage/' . $this->storage->putFileAs($path, $file, $imageName);

        $this->videosRepository->insertSimple($simpleType, $link);

        return $link;
    }

    public function unlink(Video $video)
    {
        unlink($this->storage->get($video->url));

        $video->delete();

        return true;
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\AuthorResource;
use App\Models\Authors\AuthorProfile;
use App\Models\Images;
use App\Models\SystemAuthor;
use App\Services\AuthorsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group Author Management
 *
 * Class AuthorController
 * @package App\Http\Controllers\Api\Admin
 */
class AuthorController extends ApiController
{
    /**
     * @var AuthorsService
     */
    private $authorsService;

    public function __construct(AuthorsService $authorsService)
    {
        parent::__construct();

        $this->authorsService = $authorsService;
    }

    /**
     * Get all authors
     * @responseFile responses/admin/authors.get.json
     *
     *
     * @return JsonResponse
     */
    public function get(): JsonResponse
    {
        try {
            $authors = AuthorProfile::paginate(50);
            $authorsItem = collect($authors->items())->map(fn($item) => new AuthorResource($item));
            return response()->json([
                'items' => $authorsItem,
                'current_page' => $authors->currentPage(),
                'next_page_url' => $authors->nextPageUrl(),
                'path' => $authors->path(),
                'per_page' => $authors->perPage(),
                'prev_page_url' => $authors->previousPageUrl(),
                'to' => $authors->lastItem(),
                'total' => $authors->total()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }

    }

    public function cabinet(SystemAuthor $author)
    {
    }

    /**
     * Returns single author
     *
     * @responseFile responses/admin/author.json
     *
     * @param AuthorProfile $author
     *
     * @return JsonResponse
     */
    public function find(AuthorProfile $author)
    {
        return $this->success(new AuthorResource($author));
    }

    /**
     * @bodyParam name string
     * @bobyParam description string
     * @bodyParam meta array
     * @bodyParam images array
     *
     * @responseFile responses/admin/author.json
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAuthor(Request $request)
    {
        return $this->success(['success' => true, 'deprecated' => true]);//$this->wrapCall($this->authorsService, 'create', $request);
    }

    public function updateAuthor(Request $request, AuthorProfile $author): JsonResponse
    {
        try {
            $meta = $request->get('meta');
            $author->update([
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
                'description' => $request->input('description'),
                'user_id' => $request->input('user_id'),
                'is_track' => $request->input('is_track'),
                'is_video' => $request->input('is_video'),
                'metaTitle' => $meta['title'] ?? null,
                'metaDescription' => $meta['description'] ?? null,
            ]);

            $image = $request->file('background');
            if ($image)
            {
                $pathPublic = date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR.date('d');
                $path = storage_path('app/public_html/'.$pathPublic);
                if (!file_exists($path))
                {
                    mkdir($path, 0777, true);
                }
                $filePath = $image->store($pathPublic, 'public_html');
                $backgroundImage = $author->background;
                if ($backgroundImage)
                {
                    $backgroundImage->update([
                        'url' => '/storage/'.$filePath
                    ]);
                } else {
                    Images::create([
                        'url' => '/storage/'.$filePath,
                        'type' => AuthorProfile::class,
                        'type_id' => $author->id,
                        'type_key' => 'background'
                    ]);
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }

        return response()->json(new AuthorResource($author));
    }

    public function deleteAuthor(SystemAuthor $author)
    {
        return $this->success(['success' => true, 'deprecated' => true]);
        //      return $this->wrapCall($this->authorsService, 'delete', $author);
    }
}

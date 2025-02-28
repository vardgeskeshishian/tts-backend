<?php

namespace App\Http\Controllers\System;

use Throwable;
use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Services\MetaService;
use App\Models\Structure\Blog;
use App\Services\ImagesService;
use App\Services\VideosService;
use App\Models\BlogToCategories;
use App\Services\TaggingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Api\ApiController;
use Spatie\ResponseCache\Facades\ResponseCache;

class BlogController extends ApiController
{
    protected $imagesService;
    protected $metaService;
    protected $taggingService;
    /**
     * @var VideosService
     */
    protected $videosService;

    public function __construct(
        ImagesService $imagesService,
        VideosService $videosService,
        MetaService $metaService,
        TaggingService $taggingService
    ) {
        parent::__construct();
        $this->imagesService = $imagesService;
        $this->videosService = $videosService;
        $this->metaService = $metaService;
        $this->taggingService = $taggingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function listView()
    {
        return view('admin.blog.list', [
            'list' => Blog::all(),
        ]);
    }

    public function singleView($id)
    {
        $blog = Blog::find($id);
        $authors = BlogAuthor::all();

        return view('admin.blog.single', [
            'blog' => $blog,
            'images' => $blog->getImages(),
            'categories' => $blog->categories,
            'tags' => $blog->tags,
            'meta' => $blog->getMeta(),
            'authors' => $authors,
        ]);
    }

    public function createView()
    {
        $authors = BlogAuthor::all();
        return view('admin.blog.new', compact('authors'));
    }

    public function createAction()
    {
        $meta = request()->get('meta') ?? [];
        $tags = request()->get('tags') ?? [];
        $categories = request()->get('categories');
        $images = request()->files->get('images') ?? [];
        $videos = request()->files->get('videos') ?? [];

        $data = request()->except(['meta', 'tags', 'categories', 'images']);

        $blog = new Blog();
        $blog->fill($data);
        $blog->save();

        if (!empty($meta)) {
            $this->metaService->fillInForObject($blog, $meta);
        }

        if (!empty($images)) {
            $this->imagesService->upload($blog, $images);
        }
        if (!empty($videos)) {
            $this->videosService->upload($blog, $videos);
        }
        $this->taggingService->process($blog, ['tags' => $tags]);

        BlogToCategories::where('blog_id', $blog->id)->delete();

        if ($categories) {
            foreach ($categories as $category) {
                BlogToCategories::create([
                    'blog_id' => $blog->id,
                    'category_slug' => $category,
                    'category_id' => BlogCategory::where('slug', $category)->first()->id,
                ]);
            }
        }

        Cache::forget("meta:" . Blog::class . ":{$blog->id}");
        ResponseCache::clear();

        return redirect('/system/blog/' . $blog->id);
    }

    public function updateAction($id)
    {
        $blog = Blog::find($id);

        $meta = request()->get('meta') ?? [];
        $tags = request()->only('tags') ?? [];
        $categories = request()->get('categories');
        $images = request()->files->get('images') ?? [];
        $videos = request()->files->get('videos') ?? [];

        if (!empty($meta)) {
            $this->metaService->fillInForObject($blog, $meta);
        }

        if (!empty($images)) {
            $this->imagesService->upload($blog, $images);
        }
        if (!empty($videos)) {
            $this->videosService->upload($blog, $videos);
        }
        $this->taggingService->process($blog, $tags);

        BlogToCategories::where('blog_id', $blog->id)->delete();

        if ($categories) {
            foreach ($categories as $category) {
                BlogToCategories::create([
                    'blog_id' => $blog->id,
                    'category_slug' => $category,
                    'category_id' => BlogCategory::where('slug', $category)->first()->id,
                ]);
            }
        }

        $data = request()->except(['meta', 'tags', 'categories', 'images']);

        $blog->fill($data);
        $blog->save();
        Cache::forget("meta:" . Blog::class . ":{$blog->id}");
        ResponseCache::clear();

        return redirect()->back();
    }

    /**
     * @param $id
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function deleteAction($id)
    {
        DB::transaction(function () use ($id) {
            Blog::find($id)->delete();
        });
        
        return redirect()->back();
    }

    public function uploadBlogImage()
    {
        $image = $this->imagesService->simpleUpload("blog-post", request()->file('file'), 'file');

        return response()->json([
            'location' => "https://static.taketones.com{$image}",
        ]);
    }
}

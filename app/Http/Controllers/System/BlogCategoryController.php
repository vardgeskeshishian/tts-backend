<?php

namespace App\Http\Controllers\System;

use Illuminate\Support\Str;
use App\Models\BlogCategory;
use App\Models\BlogToCategories;
use App\Http\Controllers\Api\ApiController;
use Spatie\ResponseCache\Facades\ResponseCache;

class BlogCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function listView()
    {
        return view('admin.blog-categories.list', [
            'list' => BlogCategory::all(),
        ]);
    }

    public function newView()
    {
        return view('admin.blog-categories.new');
    }

    public function editView($category)
    {
        $category = BlogCategory::find($category);

        return view('admin.blog-categories.single', compact('category'));
    }

    public function create()
    {
        $data = request()->all();

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category = BlogCategory::create($data);

        return redirect("/system/blog/categories/edit/{$category->id}");
    }

    public function update($category)
    {
        $data = request()->all();

        $category = BlogCategory::find($category);

        $oldSlug = $category->slug;

        $category->fill($data)->save();

        BlogToCategories::where('category_slug', $oldSlug)->update([
            'category_slug' => $data['slug'],
            'category_id' => $category->id,
        ]);

        ResponseCache::clear();

        return redirect()->back();
    }

    public function delete($category)
    {
        /**
         * @var $category BlogCategory
         */
        $category = BlogCategory::find($category);
        $category->delete();

        BlogToCategories::where('category_id', $category->id)->delete();

        return redirect('/system/blog/categories');
    }
}

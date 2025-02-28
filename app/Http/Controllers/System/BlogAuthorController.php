<?php

namespace App\Http\Controllers\System;

use App\Models\BlogAuthor;
use App\Models\SocialLinks;
use App\Models\SocialLinkPivot;
use App\Http\Controllers\Api\ApiController;

class BlogAuthorController extends ApiController
{
    public function listView()
    {
        $list = BlogAuthor::all();

        return view('admin.blog-authors.list', compact('list'));
    }

    public function editView($id)
    {
        $author = BlogAuthor::find($id)->load('socialLinks');
        $authorLinks = $author->socialLinks;

        $allSocials = SocialLinks::all()->map(function (SocialLinks $link) use ($authorLinks) {
            $al = $authorLinks->where('social_link_id', $link->id)->first();
            $l = clone $link;

            if ($al) {
                $l['url'] = $al->social_link_url;
            }

            return $l;
        });

        return view('admin.blog-authors.edit', compact('author', 'allSocials'));
    }

    public function newView()
    {
        $allSocials = SocialLinks::all();

        return view('admin.blog-authors.new', compact('allSocials'));
    }

    public function createAuthor()
    {
        $name = request()->get('name');

        $author = BlogAuthor::create([
            'name' => $name,
        ]);


        $this->updateLinks($author->id);

        return redirect("/system/blog/authors/{$author->id}");
    }

    public function updateAuthor($id)
    {
        $name = request()->get('name');

        $author = BlogAuthor::find($id);
        if ($author->name !== $name) {
            $author->fill(['name' => $name])->save();
        }

        $this->updateLinks($id);

        return redirect()->back();
    }

    protected function updateLinks($authorId, $links = [])
    {
        $links = request()->get('links');

        $morphData = [
            'object_class' => (new BlogAuthor())->getMorphClass(),
            'object_id' => $authorId,
        ];

        SocialLinkPivot::where($morphData)->delete();

        foreach ($links as $linkId => $link) {
            if (!$link) {
                continue;
            }

            $linkData = [
                'social_link_id' => $linkId,
                'social_link_url' => $link,
            ];

            SocialLinkPivot::create(array_merge($linkData, $morphData));
        }

        return true;
    }

}

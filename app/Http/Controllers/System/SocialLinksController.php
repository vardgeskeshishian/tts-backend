<?php

namespace App\Http\Controllers\System;

use App\Models\SocialLinks;
use App\Models\SocialLinkPivot;
use App\Services\ImagesService;
use App\Http\Controllers\Api\ApiController;

class SocialLinksController extends ApiController
{
    /**
     * @var ImagesService
     */
    private $service;

    public function __construct(ImagesService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    public function listView()
    {
        $list = SocialLinks::all();

        return view('admin.social-links.list', compact('list'));
    }

    public function newView()
    {
        return view('admin.social-links.new');
    }

    public function editView($id)
    {
        $link = SocialLinks::find($id);

        $images = $link->getImages();

        return view('admin.social-links.edit', compact('link', 'images'));
    }

    public function create()
    {
        $data = request()->except('image');
        $images = request()->files->get('images') ?? [];

        $link = SocialLinks::create($data);

        $this->service->upload($link, $images);

        $images = $link->getImages();

        return view('admin.social-links.edit', compact('link', 'images'));
    }

    public function update($id)
    {
        $data = request()->except('image');
        $images = request()->files->get('images') ?? [];

        $link = SocialLinks::find($id);
        $link->fill($data)->save();

        $this->service->upload($link, $images);

        return redirect()->back();
    }

    public function delete($id)
    {
        $link = SocialLinks::find($id);
        $link->delete();

        SocialLinkPivot::where('social_link_id', $id)->delete();

        return redirect()->back();
    }
}

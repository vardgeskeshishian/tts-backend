<?php


namespace App\Http\Controllers\System;

use App\Models\Promocode;
use App\Http\Controllers\Api\ApiController;

class PromocodesController extends ApiController
{
    public function list()
    {
        $promocodes = Promocode::paginate(15);

        return view('admin.promocodes.index', compact('promocodes'));
    }

    public function view($promocodeId)
    {
        $promocode = Promocode::find($promocodeId);

        return view('admin.promocodes.single', compact('promocode'));
    }

    public function newView()
    {
        return view('admin.promocodes.new');
    }

    public function create()
    {
        $promocode = Promocode::create(request()->all());

        return redirect()->to("/system/promocodes/{$promocode->id}");
    }

    public function update($promocodeId)
    {
        $promocode = Promocode::find($promocodeId);
        $promocode->fill(request()->except('promocodeId'))->save();

        return redirect()->back();
    }

    public function delete($promocodeId)
    {
        Promocode::find($promocodeId)->delete();

        return redirect()->to("/system/promocodes");
    }
}

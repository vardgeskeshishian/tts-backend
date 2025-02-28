<?php


namespace App\Http\Controllers\System;

use App\Models\SFX\SFXPack;
use App\Services\ImagesService;
use App\Models\SFX\SFXPackTracks;
use App\Http\Controllers\Api\ApiController;

class PackController extends ApiController
{
    public function viewList()
    {
        $list = SFXPack::whereHas('params')->paginate();

        return view('admin.packs.index', compact('list'));
    }

    public function viewCreate()
    {
        return view('admin.packs.create');
    }

    public function viewEdit($packId)
    {
        $pack = SFXPack::findOrFail($packId);

        return view('admin.packs.edit', compact('pack'));
    }

    public function createPack()
    {
        $packData = request()->except('_method', 'params', 'trackIds');
        $params = request()->get('params');
        $trackIds = request()->get('tracks');

        $pack = new SFXPack();
        $pack->fill($packData)->save();

        $params['personal'] = false;
        $pack->params()->create($params);

        SFXPackTracks::where('pack_id', $pack->id)->delete();
        foreach ($trackIds as $trackId) {
            SFXPackTracks::create(['pack_id' => $pack->id, 'sfx_track_id' => $trackId]);
        }

        $pack->refresh();

        return redirect("/system/packs/{$pack->id}");
    }

    public function updatePack($pack, ImagesService $service)
    {
        $pack = SFXPack::find($pack);
        $packData = request()->except('_method', 'params', 'trackIds');
        $params = request()->get('params');
        $trackIds = request()->get('tracks');
        $images = request()->file('images');

        $params['personal'] = false;
        $pack->fill($packData)->save();
        $pack->params()->update($params);

        SFXPackTracks::where('pack_id', $pack->id)->delete();
        foreach ($trackIds as $trackId) {
            SFXPackTracks::create(['pack_id' => $pack->id, 'sfx_track_id' => $trackId]);
        }

        if ($images) {
            $service->upload($pack, $images);
        }

        $pack->refresh();

        return redirect()->back();
    }

    public function deletePack($pack)
    {
        $pack = SFXPack::find($pack);
        $pack->delete();

        return redirect('/system/packs');
    }
}

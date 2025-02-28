<?php


namespace App\Http\Controllers\System;

use App\Models\Track;
use App\Models\SFX\SFXTrack;
use App\Http\Controllers\Api\ApiController;

class TracksController extends ApiController
{
    public function searchSoundEffects()
    {
        $query = request()->input('q.term');
        $isId = is_numeric($query);

        if ($isId) {
            return [SFXTrack::find($query)];
        }

        return SFXTrack::where('name', 'like', "%{$query}%")->get();
    }

    public function searchTracks()
    {
        $searchQuery = request()->input('q');
        $isId = is_numeric($searchQuery);

        if ($isId) {
            return [Track::find($searchQuery)];
        }

        return Track::where('name', 'like', "%{$searchQuery}%")
            ->orWhereHas('author', function ($query) use ($searchQuery) {
                $query->where('name', 'like', "%{$searchQuery}%");
            })
            ->get();
    }

    public function listView()
    {
        if (request()->has('q')) {
            $searchQuery = request()->input('q');
            $isId = is_numeric($searchQuery);

            if ($isId) {
                return [Track::find($searchQuery)];
            }

            $list = Track::where('name', 'like', "%{$searchQuery}%")
                ->orWhereHas('author', function ($query) use ($searchQuery) {
                    $query->where('name', 'like', "%{$searchQuery}%");
                })->paginate();
        } else {
            $list = Track::paginate();
        }

        return view('admin.tracks.index', compact('list'));
    }

    public function fastEditView(Track $track)
    {
        return view('admin.tracks.fast-edit', compact('track'));
    }
}

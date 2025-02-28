<?php


namespace App\Services\SFX\Pack;

use Exception;
use App\Models\User;
use App\Models\SFX\SFXPack;
use App\Models\SFX\SFXPackTracks;

class PackService
{
    /**
     * @var User
     */
    private User $user;
    private SFXPack $pack;

    /**
     * @param User $user
     * @param array $ids
     * @param string|null $name
     *
     * @return PackService
     * @throws Exception
     */
    public function newPack(User $user, array $ids, ?string $name)
    {
        return $this
            ->setUser($user)
            ->createPack($name)
            ->addTracks($ids);
    }

    private function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    private function createPack(?string $name = null)
    {
        $previousPacksCount = SFXPack::with(['params' => function ($query) {
            $query->where('created_by', $this->user->id);
        }])->count();

        $newPacksCount = $previousPacksCount + 1;

        $this->pack = SFXPack::create([
            'name' => $name ?? "Pack {$newPacksCount} by {$this->user->name}",
            'description' => "Pack {$newPacksCount} by {$this->user->name}",
            'price' => 10,
        ]);

        $this->pack->params()->create([
            'pack_id' => $this->pack->id,
            'created_by' => $this->user->id,
            'personal' => true,
        ]);

        return $this;
    }

    /**
     * @param $trackId
     *
     * @return $this
     * @throws Exception
     */
    private function addTrack($trackId)
    {
        if (SFXPackTracks::where([
            'pack_id' => $this->pack->id,
            'sfx_track_id' => $trackId,
        ])->exists()) {
            throw new Exception("Трек уже добавлен к этому паку");
        }

        SFXPackTracks::create([
            'pack_id' => $this->pack->id,
            'sfx_track_id' => $trackId,
        ]);

        return $this;
    }

    /**
     * @param int|null $packId
     *
     * @return SFXPack|null
     */
    public function getPack(?int $packId = null)
    {
        if (!$packId && !$this->pack) {
            return null;
        }

        if ($this->pack) {
            return $this->pack;
        }

        if ($packId) {
            return SFXPack::find($packId);
        }

        return null;
    }

    /**
     * @param User $user
     * @param int|null $packId
     * @param string|null $name
     *
     * @return SFXPack|null
     * @throws Exception
     */
    public function renamePack(User $user, ?int $packId, ?string $name)
    {
        $pack = SFXPack::find($packId);

        if ($pack->params->created_by !== $user->id) {
            throw new Exception("пак принадлежит другому пользователю");
        }

        $pack->name = $name;
        $pack->save();

        return $pack->fresh();
    }

    public function getUnfinishedPack(User $user)
    {
        if (!$user) {
            return null;
        }

        return SFXPack::with('params', function ($query) use ($user) {
            $query->where('created_by', $user->id);
        })->withCount('tracks')
            ->having('tracks_count', '<', 10)
            ->first();
    }

    /**
     * @param User $user
     * @param int|null $packId
     *
     * @return SFXPack
     * @throws Exception
     */
    private function findPack(User $user, ?int $packId)
    {
        if (!$packId) {
            throw new Exception("пак не найден");
        }

        $pack = SFXPack::find($packId);

        if (!$pack) {
            throw new Exception("пак не найден");
        }

        if ($pack->params->created_by !== $user->id) {
            throw new Exception("пак принадлежит другому пользователю");
        }

        return $pack;
    }

    private function addTracks(array $ids)
    {
        foreach ($ids as $id) {
            $this->addTrack($id);
        }

        return $this;
    }
}

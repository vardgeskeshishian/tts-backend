<?php

namespace App\Http\Livewire;

use App\Constants\CacheEnv;
use Cache;
use Livewire\Component;

class DisableOnlineKassa extends Component
{
    public bool $disabled = false;

    public function mount()
    {
        $this->disabled = Cache::has(CacheEnv::DISABLE_ONLINE_KASSA);
    }

    public function render()
    {
        return view('livewire.disable-online-kassa');
    }

    public function save()
    {
        if ($this->disabled) {
            Cache::forever(CacheEnv::DISABLE_ONLINE_KASSA, true);

            return;
        }

        Cache::forget(CacheEnv::DISABLE_ONLINE_KASSA);
    }
}

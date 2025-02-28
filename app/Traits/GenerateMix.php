<?php

namespace App\Traits;

use App\Models\Elastic;
use cijic\phpMorphy\Morphy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait GenerateMix
{
    private function generateMix(string $text): void
    {
        $morphy = new Morphy('en');
        $text = mb_eregi_replace( '[^a-zA-Z]', ' ', $text);
        $partsOfSpeechText = $morphy->getPartOfSpeech(explode(' ', Str::upper($text)));
        foreach ($partsOfSpeechText as $key => $arrayPart)
        {
            if (is_array($arrayPart)) {
                $arrayPart = array_diff($arrayPart, ['PREP', 'ARTICLE', 'CONJ']);
                if (count($arrayPart) == 0)
                    $text = str_replace(' '.Str::lower($key).' ', ' ', $text);
            }
        }

        $text = explode(' ', $text);

        $newText = '';

        foreach ($text as $word) {
            if ($this->id == 1516)
                Log::info($word);

            $len = strlen($word);

            if ($len <= 4) {
                $newText .= ' '.$word;
            }

            if ($len >= 5 && $len < 7) {
                $newText .= ' '.substr($word, 0, 4);
            }

            if ($len >= 7) {
                $newText .= ' '.substr($word, 0, 5);
            }
        }

        Elastic::updateOrCreate([
            'track_id' => $this->id,
            'track_type' => $this->getMorphClass(),
        ], [
            'track_id' => $this->id,
            'track_type' => $this->getMorphClass(),
            'text' => trim($newText),
        ]);
    }
}

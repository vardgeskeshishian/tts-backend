<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

trait CalculateCoefficient
{
    /**
     * @param string $text
     * @param array $splitWords
     * @param int $countDownloadsFree
     * @param int $countDownloadsSubs
     * @param int $countDownloadsAll
     * @param array $coefficients
     * @return float|int
     */
    public function calculateCoefficient(
        string $text,
        array  $splitWords,
        int $countDownloadsFree,
        int $countDownloadsSubs,
        int $countDownloadsAll,
        array $coefficients
    ): float|int
    {
        $localCoefficients = [
            'emc' => 0,
            'tmc' => 0,
        ];

        foreach ($splitWords as $key => $word) {
            $word = preg_quote($word, '\'');
            preg_match_all("/\b$word/i", $text, $match);

            $matchLength = count($match[0]);

            if (count($splitWords) > 1) {
                $localCoefficients['emc'] = $key != 0 ? min($localCoefficients['emc'], $matchLength) : $matchLength;
            }

            $localCoefficients['tmc'] += min($matchLength, $coefficients['mtc']);
        }
        $localCoefficients['emc'] = min($localCoefficients['emc'] / $coefficients['mte'], 1);
        $localCoefficients['tmc'] = count($splitWords) != 0 ? $localCoefficients['tmc'] / ($coefficients['mtc'] * count($splitWords)) : 0;

        if ($localCoefficients['emc'] || $localCoefficients['tmc'])
        {
            $age = $this->created_at->diffInDays(Carbon::now());
            $localCoefficients['n'] = max(0, min(1, ($coefficients['period_new'] - $age) / $coefficients['period_new']));
        } else {
            $localCoefficients['n'] = 0;
        }

        $localCoefficients['trend_free'] = $countDownloadsFree > 0 ? min($countDownloadsFree / $countDownloadsAll * 100, 1) : 0;
        $localCoefficients['trend_subs'] = $countDownloadsSubs > 0 ? min($countDownloadsSubs / $countDownloadsAll * 100, 1) : 0;

        $resultEmc = $localCoefficients['emc'] * $coefficients['w_emc'];
        $resultTmc = $localCoefficients['tmc'] * $coefficients['w_tmc'];
        $resultN = $localCoefficients['n'] * $coefficients['w_n'];
        $resultTrendFree = $localCoefficients['trend_free'] * $coefficients['free_coefficient'];
        $resultTrendSubs = $localCoefficients['trend_subs'] * $coefficients['subs_coefficient'];
        $result = number_format($resultEmc +
            $resultTmc +
            $resultN +
            $resultTrendFree +
            $resultTrendSubs, 5);

        $this->setAttribute('emc', (float)number_format($localCoefficients['emc'], 5));
        $this->setAttribute('tmc', (float)number_format($localCoefficients['tmc'], 5));
        $this->setAttribute('n', (float)number_format($localCoefficients['n'], 5));
        $this->setAttribute('w_emc', (float)number_format($resultEmc, 5));
        $this->setAttribute('w_tmc', (float)number_format($resultTmc, 5));
        $this->setAttribute('w_n', (float)number_format($resultN, 5));
        $this->setAttribute('w_trend_free', (float)number_format($resultTrendFree, 5));
        $this->setAttribute('w_trend_subs', (float)number_format($resultTrendSubs, 5));
        $this->setAttribute('trending', (float)$result);
        return $result;
    }
}
<?php


namespace App\Services\Finance;

use Carbon\Carbon;
use App\Models\UserDownloads;
use App\Models\Authors\Author;
use App\Models\Finance\HistoryStatement;

class StatementService
{
    public function getAuthorShareForDate(Author $author, string $date)
    {
        $statement = HistoryStatement::where([
            'date' => FinanceService::getFinanceDate($date),
            'author_id' => $author->id,
        ])->first();

        if (!$statement) {
            $statement = $this->calculateAuthorShareForDate($author, $date);
        }

        return $statement->share ?? 0;
    }

    public function calculateAuthorShareForDate(Author $author, string $date)
    {
        $date = Carbon::parse($date)->startOfMonth()->startOfDay();

        // todo: one month
        $previousMonth = Carbon::parse($date)->subMonths()->startOfDay();

        $downloads = UserDownloads::select(['id', 'track_id'])->where('type', '!=', 'preview-download')
            ->whereNotNull('license_id')
            ->where('created_at', '>=', $previousMonth)
            ->where('created_at', '<', $date)
            ->get();

        $totalDownloads = $downloads->count();

        $tracks = $author->getAuthorTracks()->pluck('id');

        $authorDownloads = $downloads->whereIn('track_id', $tracks)->count();

        $authorShare = ($totalDownloads > 0 ? ($authorDownloads / $totalDownloads) * 100 : 0);

        return HistoryStatement::create([
            'date' => FinanceService::getFinanceDate($date),
            'author_id' => $author->id,
            'share' => $authorShare,
        ]);
    }
}

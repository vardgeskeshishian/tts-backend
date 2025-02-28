<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Constants\LicenseConstants;
use App\Constants\Env;
use App\Models\License;
use App\Models\Track;
use App\Models\User;
use App\Models\OrderItem;
use Maatwebsite\Excel\Excel;
use App\Models\Structure\Collection;
use Maatwebsite\Excel\Concerns\FromQuery;
use App\Models\Structure\CollectionTrack;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

class TracksActivityExport implements Responsable, FromQuery, WithMapping, WithHeadings, WithCustomChunkSize
{
    use Exportable;

    private $fileName;
    private $writerType = Excel::CSV;
    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    private $dateFrom;
    private $dateTo;

    private $licenses;

    public function __construct($dateFrom = null, $dateTo = null)
    {
        $this->dateFrom = $dateFrom ? Carbon::createFromFormat('Y-m-d', $dateFrom) : Carbon::now()->startOfMonth();
        $this->dateTo = $dateTo ? Carbon::createFromFormat('Y-m-d', $dateTo) : Carbon::now()->endOfMonth();

        $this->fileName .= $this->dateFrom . " - " . $this->dateTo;

        $this->licenses = License::where([
            'payment_type' => LicenseConstants::STANDARD_LICENSE,
        ])->whereHas('standard', function ($q) {
            return $q->where('price', '>', 0);
        })->select(['id', 'type'])->get()->mapWithKeys(function ($item) {
            return [
                $item->id => $item->type,
            ];
        });
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function query()
    {
        return Track::disableCache()->with('orderItem', 'author');
    }

    /**
     * @param User $user
     *
     * @return array
     */

    /**
     * @param Track $track
     *
     * @return array
     */
    public function map($track): array
    {
        $mapping = [
            'track' => $track->name,
            'author' => optional($track->author)->name ?? "anonymous",
            'hidden/comm' => ($track->hidden ? "Y" : "N") . " / " . ($track->premium ? "Y" : "N"),
        ];

        $licenseSpreading = [];
        foreach ($this->licenses as $key => $name) {
            $licenseSpreading[$key] = 0;
        }
        $totalSum = 0;

        $downloads = $track->userDownloads()->whereBetween('updated_at', [
            $this->dateFrom,
            $this->dateTo,
        ])->get();
//
        $mapping['preview-count'] = $downloads->where('type', 'preview-download')->count() ?? 0;
        $mapping['free-download-count'] = $downloads->where('license_id')
                ->where('type', '!=', 'preview-download')
                ->count() ?? 0;
        $mapping['sub-count'] = $downloads->where('license_id', '!=')->count() ?? 0;

        $collectionIds = CollectionTrack::where('track_id', $track->id)->pluck('collection_id')->all();
        $mapping['collection'] = Collection::whereIn('id', $collectionIds)->get()->pluck('name')->implode(',');

        $mapping['created-at'] = $track->created_at;

        /**
         * @var $item OrderItem
         */
        $track->orderItem()
            ->newQuery()
            ->whereHas('order', function ($q) {
                return $q->where('status', Env::STATUS_FINISHED);
            })
            ->whereIn('license_id', [array_keys($licenseSpreading)])
            ->whereBetween('updated_at', [$this->dateFrom, $this->dateTo])
            ->chunk(100, function ($items) use (&$licenseSpreading, &$totalSum) {
                foreach ($items as $item) {
                    $licenseSpreading[$item->license_id]++;

                    $totalSum += $item->price;
                }
            });

        foreach ($licenseSpreading as $key => $count) {
            $mapping[$this->licenses[$key]] = $count;
        }

        $mapping['total-sum'] = $totalSum;

        return array_values($mapping);
    }

    public function headings(): array
    {
        $heading = [
            'Track Name',
            'Author',
            'Self/Comm',
            'Is Main Page',
            'Preview Downloads',
            'Free Downloads',
            'Sub Downloads',
            'Collection',
            'Created At',
        ];

        foreach ($this->licenses as $name) {
            $heading[] = $name;
        }

        $heading[] = 'Total Sum';

        return $heading;
    }
}

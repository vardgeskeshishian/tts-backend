<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithCustomQuerySize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Excel;

class UsersActivityExport implements Responsable, FromQuery, WithMapping, WithHeadings, WithCustomQuerySize, WithCustomChunkSize
{
    use Exportable;

    private $fileName = "users-activity.csv";
    private $writerType = Excel::CSV;
    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    private $dateFrom = null;
    private $dateTo = null;
    private $subHistory = [];

    public function __construct($dateFrom = null, $dateTo = null)
    {
        if ($dateFrom) {
            $this->dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom);
        }

        if ($dateTo) {
            $this->dateTo = Carbon::createFromFormat('Y-m-d', $dateTo);
        }

        if (!$dateFrom && $dateTo) {
            $this->dateFrom = Carbon::create(1970);
        }

        if ($dateFrom && !$dateTo) {
            $this->dateTo = Carbon::now();
        }
    }

    public function querySize(): int
    {
        return 250;
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public function query()
    {
        return User::query()->with('type', 'finishedOrders', 'subscription', 'subHistory', 'downloaded');
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function map($user): array
    {
        $mapping = [
            'email'         => $user->email,
            'type'          => optional($user->type)->name ?? "no type",
            'sub-status'    => optional($user->subscription)->status ?? "non subscribed",
        ];

        $mapping['created-at'] = $user->created_at;

        if (!$this->dateFrom && !$this->dateTo) {
            $mapping['order-count'] = $user->finishedOrders->count() ?? 0;
            $mapping['order-sum'] = $user->finishedOrders->sum('total') ?? 0;
            $mapping['subs-sum'] = $user->subHistory->sum('payment') ?? 0;
            $mapping['last-purchase'] = optional($user->finishedOrders->sortByDesc('updated_at')->first())->updated_at;
            $mapping['last-preview'] = $user->last_preview_download;
            $mapping['last-free'] = optional($user->downloaded->where('license_id')->sortByDesc('updated_at')->first())->updated_at;
            $mapping['free-download'] = $user->downloaded->where('license_id')->count();
            $mapping['sub-download'] = $user->downloaded->where('license_id', '!=')->count();

            return array_values($mapping);
        }

        $orders = $user->finishedOrders->whereBetween('updated_at', [$this->dateFrom, $this->dateTo]);
        $subHistory = $user->subHistory->whereBetween('updated_at', [$this->dateFrom, $this->dateTo]);
        $downloaded = $user->downloaded->whereBetween('updated_at', [$this->dateFrom, $this->dateTo]);

        $mapping['order-count'] = $orders->count() ?? 0;
        $mapping['order-sum'] = $orders->sum('total') ?? 0;
        $mapping['subs-sum'] = $subHistory->sum('payment') ?? 0;
        $mapping['last-purchase'] = optional($user->finishedOrders->sortByDesc('updated_at')->first())->updated_at;
        $mapping['last-preview'] = $user->last_preview_download;
        $mapping['last-free'] = optional($user->downloaded->where('license_id')->sortByDesc('updated_at')->first())->updated_at;
        $mapping['free-download'] = $downloaded->where('license_id')->count();
        $mapping['sub-download'] = $downloaded->where('license_id', '!=')->count();

        $this->fileName .= $this->dateFrom . " - " . $this->dateTo;

        return array_values($mapping);
    }

    public function headings(): array
    {
        return [
            'E-mail',
            'Type',
            'Total Orders',
            'Total Orders Sum',
            'Sub Status',
            'Subs Sum',
            'Created At',
            'Last Purchase',
            'Last Preview',
            'Last Free',
            'Free Download',
            'Sub Download',
        ];
    }
}

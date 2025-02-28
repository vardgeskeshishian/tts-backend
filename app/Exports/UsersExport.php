<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;

class UsersExport implements Responsable, FromQuery, WithHeadings
{
    use Exportable;

    private $fileName = "users.csv";
    private $writerType = Excel::CSV;
    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    public function query()
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return User::query()->select('id', 'email', 'role', 'downloads', 'created_at');
    }

    public function headings(): array
    {
        return [
            'id',
            'email',
            'role',
            'downloads',
            'created at',
        ];
    }
}

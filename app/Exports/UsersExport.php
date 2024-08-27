<?php

namespace Modules\Usermanagement\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Modules\Usermanagement\Models\User;

class UsersExport implements WithColumnFormatting, WithHeadings, FromCollection, withMapping
{
    public function collection(){
        return User::all();
    }

    public function map($row): array{
        return [
            $row->id,
            $row->name,
            $row->email,
            $row->nik,
            $row->branch->name,
            $row->created_at
        ];
    }
    public function headings(): array{
        return [
            'ID',
            'Name',
            'Email',
            'NIK',
            'Branch',
            'Created At'
        ];
    }

    public function columnFormats(): array{
        return [
            'A' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'F' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
        ];
    }
}

<?php

namespace Modules\Usermanagement\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Usermanagement\Models\Role;

class RolesExport implements WithColumnFormatting, WithHeadings, FromCollection, withMapping
{
    public function collection(){
        return Role::with('position')->get();
    }

    public function map($row): array{
        return [
            $row->id,
            $row->name,
            $row->position ? $row->position->name : '-',
            $row->position ? $row->position->level : '-',
            $row->created_at
        ];
    }
    public function headings(): array{
        return [
            'ID',
            'Role',
            'Position',
            'Tingkat Jabatan',
            'Created At'
        ];
    }

    public function columnFormats(): array{
        return [
            'A' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'D' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'E' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
        ];
    }
}

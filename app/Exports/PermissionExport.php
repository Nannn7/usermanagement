<?php

namespace Modules\Usermanagement\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Usermanagement\Models\PermissionGroup;

class PermissionExport implements WithColumnFormatting, WithHeadings, FromCollection, withMapping
{
    public function collection(){
        $permission = PermissionGroup::all();

        return $permission->map(function ($permission) {
            $permission->roles = $permission->roles($permission);

            return $permission;
        });
    }

    public function map($row): array{
        // Convert the array to a collection before using pluck
        $role = collect($row->roles)->pluck('name')->toArray();
        return [
            $row->id,
            $row->name,
            $row->roles == null? '' : implode(', ', $role),
            $row->created_at
        ];
    }
    public function headings(): array{
        return [
            'ID',
            'Permission',
            'Roles',
            'Created At'
        ];
    }

    public function columnFormats(): array{
        return [
            'A' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'C' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
        ];
    }
}

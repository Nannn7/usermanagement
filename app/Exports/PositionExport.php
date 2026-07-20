<?php

namespace Modules\Usermanagement\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Usermanagement\Models\Position;

class PositionExport implements WithColumnFormatting, WithHeadings, FromCollection, WithMapping
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = Position::query();

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('CAST(level AS TEXT) LIKE ?', ['%' . $search . '%']);
            });
        }

        return $query->get();
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->code,
            $row->name,
            $row->level,
            $row->created_at
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Name',
            'Tingkat Jabatan',
            'Created At'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'D' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'E' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
        ];
    }
}

<?php

namespace Modules\Usermanagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Usermanagement\Models\Position;

class PositionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->data() as $value) {
            $position = Position::withTrashed()->updateOrCreate(
                ['code' => $value['code']],
                [
                    'name' => $value['name'],
                    'level' => $value['level'],
                ]
            );

            if ($position->trashed()) {
                $position->restore();
            }
        }
    }

    public function data(): array
    {
        return [
            ['code' => '001', 'name' => 'Staff', 'level' => 1],
            ['code' => '002', 'name' => 'Executive Officer', 'level' => 2],
            ['code' => '003', 'name' => 'Deputy Director', 'level' => 3],
            ['code' => '004', 'name' => 'Assistant Director', 'level' => 4],
            ['code' => '005', 'name' => 'Chief Operation Officer', 'level' => 5],
            ['code' => '006', 'name' => 'Chief Technology Officer', 'level' => 5],
            ['code' => '007', 'name' => 'Vice Head of Bureau', 'level' => 5],
            ['code' => '008', 'name' => 'Head of Bureau', 'level' => 6],
            ['code' => '009', 'name' => 'Director', 'level' => 6],
            ['code' => '010', 'name' => 'Secretary', 'level' => 6],
            ['code' => '011', 'name' => 'Vice President Director', 'level' => 7],
            ['code' => '012', 'name' => 'President Director', 'level' => 8],
            ['code' => '013', 'name' => 'Commisioner', 'level' => 9],
        ];
    }
}

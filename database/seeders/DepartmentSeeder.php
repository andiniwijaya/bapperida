<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [

            [
                'code' => 'BAPPERIDA',
                'name' => 'Badan Perencanaan Pembangunan Riset dan Inovasi Daerah',
            ],

            [
                'code' => 'SEKRET',
                'name' => 'Sekretariat',
            ],

            [
                'code' => 'PPEPD',
                'name' => 'Perencanaan Pengendalian dan Evaluasi Pembangunan Daerah',
            ],

            [
                'code' => 'PPMP',
                'name' => 'Perencanaan Pembangunan Manusia dan Pemerintahan',
            ],

            [
                'code' => 'PSDA',
                'name' => 'Perencanaan Pembangunan Perekonomian dan Sumber Daya Alam',
            ],

            [
                'code' => 'INFRASWIL',
                'name' => 'Infrastruktur dan Kewilayahan',
            ],

            [
                'code' => 'RIDA',
                'name' => 'Riset dan Inovasi Daerah',
            ],

        ];

        foreach ($departments as $department) {
            $model = Department::query()->updateOrCreate(
                ['code' => $department['code']],
                ['name' => $department['name']]
            );

            $model->is_active = true;
            $model->save();
        }
    }
}

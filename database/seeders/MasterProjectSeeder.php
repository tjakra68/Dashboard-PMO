<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MasterProjectSeeder extends Seeder
{
    /**
     * Import the master list workbook when it is available locally.
     */
    public function run(): void
    {
        $file = database_path('data/master-list.xlsx');

        if (! is_file($file)) {
            $this->command?->warn("Master list workbook not found at {$file}, skipping import.");

            return;
        }

        $this->command?->call('master-list:import', ['file' => $file]);
    }
}

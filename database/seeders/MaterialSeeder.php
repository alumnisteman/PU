<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
{
    public function run()
    {
        DB::table('materials')->insert([
            ['name' => 'Aspal', 'unit' => 'ton', 'price' => 1500000],
            ['name' => 'Batu Split', 'unit' => 'm3', 'price' => 300000],
            ['name' => 'Pasir', 'unit' => 'm3', 'price' => 250000],
            ['name' => 'Kerikil', 'unit' => 'm3', 'price' => 280000],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishers = [
            ['name' => 'Editorial Nacional', 'city' => 'Lima', 'country' => 'Perú', 'is_active' => true],
            ['name' => 'Fondo Editorial PUCP', 'city' => 'Lima', 'country' => 'Perú', 'is_active' => true],
            ['name' => 'Editorial UNMSM', 'city' => 'Lima', 'country' => 'Perú', 'is_active' => true],
            ['name' => 'Penguin Random House', 'city' => 'Madrid', 'country' => 'España', 'is_active' => true],
            ['name' => 'HarperCollins', 'city' => 'New York', 'country' => 'USA', 'is_active' => true],
            ['name' => 'Editorial Planeta', 'city' => 'Barcelona', 'country' => 'España', 'is_active' => true],
        ];

        DB::table('publishers')->insert($publishers);
    }
}

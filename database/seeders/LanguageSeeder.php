<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'is_active' => true],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_active' => true],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'is_active' => true],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'is_active' => true],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'is_active' => true],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'is_active' => true],
            ['code' => 'qu', 'name' => 'Quechua', 'native_name' => 'Runa Simi', 'is_active' => true],
            ['code' => 'ay', 'name' => 'Aymara', 'native_name' => 'Aymar aru', 'is_active' => true],
        ];

        DB::table('languages')->insert($languages);
    }
}

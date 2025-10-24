<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Tecnología', 'slug' => 'tecnologia', 'description' => 'Libros de tecnología e informática', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Ciencias', 'slug' => 'ciencias', 'description' => 'Libros de ciencias naturales y exactas', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Literatura', 'slug' => 'literatura', 'description' => 'Obras literarias y narrativa', 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Historia', 'slug' => 'historia', 'description' => 'Libros de historia y ciencias sociales', 'sort_order' => 4, 'is_active' => true],
            ['name' => 'Arte', 'slug' => 'arte', 'description' => 'Libros de arte y diseño', 'sort_order' => 5, 'is_active' => true],
            ['name' => 'Educación', 'slug' => 'educacion', 'description' => 'Libros sobre pedagogía y educación', 'sort_order' => 6, 'is_active' => true],
            ['name' => 'Medicina', 'slug' => 'medicina', 'description' => 'Libros de medicina y salud', 'sort_order' => 7, 'is_active' => true],
            ['name' => 'Derecho', 'slug' => 'derecho', 'description' => 'Libros de derecho y legislación', 'sort_order' => 8, 'is_active' => true],
        ];

        DB::table('categories')->insert($categories);
    }
}

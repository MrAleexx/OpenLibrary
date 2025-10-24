<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publisher = Publisher::first();
        $categories = Category::all();

        $books = [
            [
                'title' => 'Introducción a la Programación con Python',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012347',
                'language_code' => 'es',
                'pages' => 350,
                'publication_year' => 2024,
                'cover_image'=> 'covers/python.jpg',
                'pdf_file' => 'books/python.pdf',
                'book_type' => 'digital',
                'featured' => true,
                'access_level' => 'free',
                'copyright_status' => 'creative_commons',
                'license_type' => 'CC BY-NC-SA',
            ],
            [
                'title' => 'Historia del Perú Contemporáneo',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012354',
                'language_code' => 'es',
                'pages' => 280,
                'publication_year' => 2023,
                'cover_image' => 'covers/historia.jpg',
                'pdf_file' => 'books/historia.pdf',
                'book_type' => 'both',
                'total_physical_copies' => 5,
                'available_physical_copies' => 5,
                'featured' => true,
                'access_level' => 'free',
                'copyright_status' => 'copyrighted',
            ],
        ];

        foreach ($books as $bookData) {
            $book = Book::create($bookData);
            
            // Asignar categorías aleatorias
            $book->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Crear detalles del libro
            BookDetail::create([
                'book_id' => $book->id,
                'description' => "Descripción detallada del libro {$book->title}",
                'edition' => '1ra Edición',
                'file_format' => 'PDF',
                'file_size' => '2.5 MB',
                'keywords' => 'programación, python, desarrollo',
                'acquisition_date' => now()->subMonths(rand(1, 12)),
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookContributor;
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
                'is_active' => true,
            ],
            [
                'title' => 'Clean Code: Manual de Estilo para el Desarrollo de Software',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012354',
                'language_code' => 'es',
                'pages' => 464,
                'publication_year' => 2023,
                'cover_image' => 'covers/clean-code.jpg',
                'pdf_file' => 'books/clean-code.pdf',
                'book_type' => 'both',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Historia del Perú Contemporáneo',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012361',
                'language_code' => 'es',
                'pages' => 280,
                'publication_year' => 2022,
                'cover_image' => 'covers/historia-peru.jpg',
                'pdf_file' => null,
                'book_type' => 'physical',
                'featured' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Don Quijote de la Mancha',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012378',
                'language_code' => 'es',
                'pages' => 863,
                'publication_year' => 2021,
                'cover_image' => 'covers/quijote.jpg',
                'pdf_file' => null,
                'book_type' => 'physical',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Sapiens: De Animales a Dioses',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012385',
                'language_code' => 'es',
                'pages' => 496,
                'publication_year' => 2023,
                'cover_image' => 'covers/sapiens.jpg',
                'pdf_file' => 'books/sapiens.pdf',
                'book_type' => 'both',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'La Casa de los Espíritus',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012392',
                'language_code' => 'es',
                'pages' => 433,
                'publication_year' => 2020,
                'cover_image' => 'covers/casa-espiritus.jpg',
                'pdf_file' => 'books/casa-espiritus.pdf',
                'book_type' => 'digital',
                'featured' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Atomic Habits: Pequeños Cambios, Resultados Extraordinarios',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012408',
                'language_code' => 'es',
                'pages' => 320,
                'publication_year' => 2024,
                'cover_image' => 'covers/atomic-habits.jpg',
                'pdf_file' => 'books/atomic-habits.pdf',
                'book_type' => 'both',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Algoritmos y Estructuras de Datos',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012415',
                'language_code' => 'es',
                'pages' => 512,
                'publication_year' => 2023,
                'cover_image' => 'covers/algoritmos.jpg',
                'pdf_file' => 'books/algoritmos.pdf',
                'book_type' => 'digital',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'La Revolución Francesa: Una Historia',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012422',
                'language_code' => 'es',
                'pages' => 356,
                'publication_year' => 2021,
                'cover_image' => 'covers/revolucion-francesa.jpg',
                'pdf_file' => null,
                'book_type' => 'physical',
                'featured' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Inteligencia Artificial: Un Enfoque Moderno',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012439',
                'language_code' => 'es',
                'pages' => 1152,
                'publication_year' => 2024,
                'cover_image' => 'covers/ia-moderno.jpg',
                'pdf_file' => 'books/ia-moderno.pdf',
                'book_type' => 'both',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'El Principito',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012446',
                'language_code' => 'es',
                'pages' => 96,
                'publication_year' => 2020,
                'cover_image' => 'covers/principito.jpg',
                'pdf_file' => 'books/principito.pdf',
                'book_type' => 'digital',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Cien Años de Soledad',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012453',
                'language_code' => 'es',
                'pages' => 471,
                'publication_year' => 2022,
                'cover_image' => 'covers/cien-anos.jpg',
                'pdf_file' => null,
                'book_type' => 'physical',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Diseño de Patrones: Elementos de Software Reutilizable',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012460',
                'language_code' => 'es',
                'pages' => 395,
                'publication_year' => 2023,
                'cover_image' => 'covers/design-patterns.jpg',
                'pdf_file' => 'books/design-patterns.pdf',
                'book_type' => 'digital',
                'featured' => false,
                'is_active' => true,
            ],
            [
                'title' => 'El Arte de la Guerra',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012477',
                'language_code' => 'es',
                'pages' => 112,
                'publication_year' => 2021,
                'cover_image' => 'covers/arte-guerra.jpg',
                'pdf_file' => 'books/arte-guerra.pdf',
                'book_type' => 'both',
                'featured' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Machine Learning con Python',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012484',
                'language_code' => 'es',
                'pages' => 478,
                'publication_year' => 2024,
                'cover_image' => 'covers/ml-python.jpg',
                'pdf_file' => 'books/ml-python.pdf',
                'book_type' => 'digital',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => '1984',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012491',
                'language_code' => 'es',
                'pages' => 328,
                'publication_year' => 2020,
                'cover_image' => 'covers/1984.jpg',
                'pdf_file' => null,
                'book_type' => 'physical',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Desarrollo Web Full Stack con Laravel y Vue',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012507',
                'language_code' => 'es',
                'pages' => 589,
                'publication_year' => 2024,
                'cover_image' => 'covers/laravel-vue.jpg',
                'pdf_file' => 'books/laravel-vue.pdf',
                'book_type' => 'both',
                'featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'El Código Da Vinci',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012514',
                'language_code' => 'es',
                'pages' => 489,
                'publication_year' => 2022,
                'cover_image' => 'covers/codigo-davinci.jpg',
                'pdf_file' => 'books/codigo-davinci.pdf',
                'book_type' => 'digital',
                'featured' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Bases de Datos: Diseño e Implementación',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012521',
                'language_code' => 'es',
                'pages' => 425,
                'publication_year' => 2023,
                'cover_image' => 'covers/bases-datos.jpg',
                'pdf_file' => 'books/bases-datos.pdf',
                'book_type' => 'both',
                'featured' => false,
                'is_active' => true,
            ],
            [
                'title' => 'El Alquimista',
                'publisher_id' => $publisher->id,
                'isbn' => '9786120012538',
                'language_code' => 'es',
                'pages' => 192,
                'publication_year' => 2021,
                'cover_image' => 'covers/alquimista.jpg',
                'pdf_file' => 'books/alquimista.pdf',
                'book_type' => 'digital',
                'featured' => true,
                'is_active' => true,
            ],
        ];

        DB::transaction(function () use ($books, $categories) {
            foreach ($books as $bookData) {
                $book = Book::create($bookData);
                
                // Asignar categorías aleatorias (1-3 categorías por libro)
                $book->categories()->attach(
                    $categories->random(rand(1, min(3, $categories->count())))->pluck('id')->toArray()
                );

                // Crear detalles del libro
                BookDetail::create([
                    'book_id' => $book->id,
                    'description' => "Descripción detallada del libro {$book->title}. Este libro es una excelente adición a cualquier biblioteca.",
                    'edition' => rand(1, 5) . 'ra Edición',
                    'file_format' => $book->book_type !== 'physical' ? 'PDF' : 'Physical',
                    'file_size' => $book->book_type !== 'physical' ? rand(1, 10) . '.' . rand(0, 9) . ' MB' : null,
                    'keywords' => $this->generateKeywords($book->title),
                    'acquisition_date' => now()->subMonths(rand(1, 24)),
                ]);

                // Crear contribuidores (autores)
                BookContributor::create([
                    'book_id' => $book->id,
                    'full_name' => $this->generateAuthorName($book->title),
                    'contributor_type' => 'author',
                    'sequence_number' => 1,
                ]);
            }
        });
    }

    /**
     * Genera palabras clave basadas en el título
     */
    private function generateKeywords(string $title): string
    {
        $keywords = explode(' ', strtolower($title));
        return implode(', ', array_slice($keywords, 0, min(5, count($keywords))));
    }

    /**
     * Genera un nombre de autor ficticio basado en el título
     */
    private function generateAuthorName(string $title): string
    {
        $authors = [
            'Python' => 'John Smith',
            'Clean Code' => 'Robert C. Martin',
            'Historia' => 'Carlos Contreras',
            'Quijote' => 'Miguel de Cervantes',
            'Sapiens' => 'Yuval Noah Harari',
            'Casa de los Espíritus' => 'Isabel Allende',
            'Atomic Habits' => 'James Clear',
            'Algoritmos' => 'Thomas Cormen',
            'Revolución Francesa' => 'Eric Hobsbawm',
            'Inteligencia Artificial' => 'Stuart Russell',
            'Principito' => 'Antoine de Saint-Exupéry',
            'Cien Años' => 'Gabriel García Márquez',
            'Diseño' => 'Gang of Four',
            'Arte de la Guerra' => 'Sun Tzu',
            'Machine Learning' => 'Andreas Müller',
            '1984' => 'George Orwell',
            'Laravel' => 'Daniel Pecos',
            'Código Da Vinci' => 'Dan Brown',
            'Bases de Datos' => 'Ramez Elmasri',
            'Alquimista' => 'Paulo Coelho',
        ];

        foreach ($authors as $keyword => $author) {
            if (stripos($title, $keyword) !== false) {
                return $author;
            }
        }

        return 'Autor Desconocido';
    }
}

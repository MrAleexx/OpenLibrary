<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\PhysicalCopy;
use Illuminate\Database\Seeder;

class PhysicalCopySeeder extends Seeder
{
    public function run(): void
    {
        // Obtener libros físicos o mixtos
        $physicalBooks = Book::whereIn('book_type', ['physical', 'both'])->get();

        foreach ($physicalBooks as $book) {
            // Crear 1-5 copias físicas para cada libro
            $copyCount = rand(1, 5);
            
            for ($i = 1; $i <= $copyCount; $i++) {
                PhysicalCopy::create([
                    'book_id' => $book->id,
                    'barcode' => 'BC-' . $book->isbn . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'copy_number' => $i,
                    'status' => 'available',
                    'location' => 'Estantería ' . rand(1, 10),
                    'location_section' => 'Sección ' . chr(65 + rand(0, 3)), // A, B, C, D
                    'location_shelf' => 'Balda ' . rand(1, 5),
                    'acquisition_date' => now()->subDays(rand(1, 365)),
                    'acquisition_cost' => rand(50, 200),
                    'condition' => 'good',
                ]);
            }

            // Actualizar contadores del libro
            $book->updatePhysicalCopiesCount();
        }
    }
}

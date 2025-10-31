<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\PhysicalCopy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Comando para sincronizar copias físicas de libros
 * 
 * Este comando:
 * 1. Encuentra libros tipo 'physical' o 'both' sin copias físicas
 * 2. Crea 3-5 copias físicas por libro con códigos de barras únicos
 * 3. Actualiza los contadores en la tabla books
 * 
 * Uso:
 *   php artisan books:sync-physical-copies
 * 
 * Cuándo ejecutar:
 *   - Después de correr seeders: php artisan db:seed
 *   - Cuando los contadores estén desincronizados
 *   - Después de crear libros manualmente
 * 
 * Características:
 *   - Idempotente: Puedes ejecutarlo múltiples veces sin duplicar
 *   - Usa transacciones: Si falla, se revierte todo
 *   - Solo crea si no existen copias
 * 
 * @author GitHub Copilot
 * @date 2025-10-31
 */
class SyncPhysicalCopies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:sync-physical-copies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear copias físicas para libros tipo physical o both y actualizar contadores';

    /**
     * Número mínimo de copias a crear por libro
     */
    private const MIN_COPIES = 3;

    /**
     * Número máximo de copias a crear por libro
     */
    private const MAX_COPIES = 5;

    /**
     * Execute the console command.
     * 
     * @return int Exit code (0 = success, 1 = error)
     */
    public function handle(): int
    {
        $this->info('🔄 Sincronizando copias físicas...');

        DB::transaction(function () {
            // Obtener libros que deberían tener copias físicas
            $books = Book::whereIn('book_type', ['physical', 'both'])
                ->where('is_active', true)
                ->get();

            $this->info("📚 Encontrados {$books->count()} libros con copias físicas");

            $createdCount = 0;
            $updatedCount = 0;

            foreach ($books as $book) {
                // Verificar si ya tiene copias físicas
                $existingCopies = $book->physicalCopies()->count();

                if ($existingCopies === 0) {
                    // Crear copias físicas aleatorias para cada libro
                    $copiesToCreate = rand(self::MIN_COPIES, self::MAX_COPIES);

                    for ($i = 1; $i <= $copiesToCreate; $i++) {
                        PhysicalCopy::create([
                            'book_id' => $book->id,
                            // Formato: BC00001-001, BC00001-002, etc.
                            'barcode' => 'BC' . str_pad($book->id, 5, '0', STR_PAD_LEFT) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                            // Dejar la última copia como prestada para simular realismo
                            'status' => $i <= ($copiesToCreate - 1) ? 'available' : 'loaned',
                            'condition' => 'good',
                            // Ubicación calculada: Estantería A-1, B-5, etc.
                            'location' => 'Estantería ' . chr(65 + ($book->id % 10)) . '-' . (($book->id % 20) + 1),
                            'notes' => 'Copia física generada automáticamente por SyncPhysicalCopies',
                            // Fecha de adquisición entre 1-24 meses atrás
                            'acquisition_date' => now()->subMonths(rand(1, 24)),
                        ]);
                    }

                    $createdCount += $copiesToCreate;
                    $this->line("  ✅ {$book->title}: {$copiesToCreate} copias creadas");
                }

                // Actualizar contadores en el libro
                // IMPORTANTE: Usar nombres de columnas reales de la DB
                $totalCopies = $book->physicalCopies()->count();
                $availableCopies = $book->physicalCopies()->where('status', 'available')->count();

                $book->update([
                    'total_physical_copies' => $totalCopies,        // Columna real en DB
                    'available_physical_copies' => $availableCopies,  // Columna real en DB
                ]);

                $updatedCount++;
            }

            $this->newLine();
            $this->info("✅ Proceso completado:");
            $this->info("   - Copias físicas creadas: {$createdCount}");
            $this->info("   - Libros actualizados: {$updatedCount}");
        });

        return 0;
    }
}

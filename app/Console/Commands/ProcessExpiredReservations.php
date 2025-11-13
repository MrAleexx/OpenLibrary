<?php

namespace App\Console\Commands;

use App\Models\BookReservation;
use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Comando para procesar reservas expiradas automáticamente
 * 
 * Este comando debe ejecutarse diariamente mediante cron job.
 * Marca como expiradas todas las reservas en estado 'ready' que
 * han pasado su fecha límite de recogida (pickup_deadline).
 * 
 * También restaura la disponibilidad de libros cuando se expiran reservas.
 * 
 * Uso: php artisan reservations:process-expired
 */
class ProcessExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa y marca como expiradas todas las reservas que han pasado su fecha límite de recogida';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Buscando reservas expiradas...');

        try {
            // Obtener reservas expiradas
            $expiredReservations = BookReservation::where('status', 'ready')
                ->where('pickup_deadline', '<', now())
                ->get();

            if ($expiredReservations->isEmpty()) {
                $this->info('✅ No hay reservas expiradas.');
                return Command::SUCCESS;
            }

            $this->info("📋 Encontradas {$expiredReservations->count()} reservas expiradas.");
            
            $processed = 0;
            $errors = 0;

            // Crear una barra de progreso
            $bar = $this->output->createProgressBar($expiredReservations->count());
            $bar->start();

            foreach ($expiredReservations as $reservation) {
                try {
                    DB::beginTransaction();

                    // Marcar reserva como expirada
                    $reservation->update([
                        'status' => 'expired',
                        'notes' => $this->appendNote(
                            $reservation->notes,
                            'Expirada automáticamente el ' . now()->format('d/m/Y H:i')
                        ),
                    ]);

                    // Restaurar disponibilidad del libro
                    $this->restoreBookAvailability($reservation->book_id);

                    DB::commit();

                    $processed++;

                    // Log del proceso
                    Log::info('Reserva expirada procesada', [
                        'reservation_id' => $reservation->id,
                        'user_id' => $reservation->user_id,
                        'book_id' => $reservation->book_id,
                        'pickup_deadline' => $reservation->pickup_deadline,
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors++;

                    Log::error('Error al procesar reserva expirada', [
                        'reservation_id' => $reservation->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Mostrar resumen
            $this->info("✅ Proceso completado:");
            $this->table(
                ['Métrica', 'Cantidad'],
                [
                    ['Reservas encontradas', $expiredReservations->count()],
                    ['Reservas procesadas', $processed],
                    ['Errores', $errors],
                ]
            );

            if ($errors > 0) {
                $this->warn("⚠️  Se encontraron {$errors} errores. Revisa los logs para más detalles.");
                return Command::FAILURE;
            }

            $this->info('🎉 Todas las reservas expiradas fueron procesadas exitosamente.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error al procesar reservas expiradas: ' . $e->getMessage());
            Log::error('Error crítico en ProcessExpiredReservations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Restaurar la disponibilidad de un libro cuando se expira una reserva
     * 
     * @param int $bookId
     * @return void
     */
    private function restoreBookAvailability(int $bookId): void
    {
        $book = Book::find($bookId);

        if ($book && $book->available_physical_copies < $book->total_physical_copies) {
            $book->increment('available_physical_copies');

            Log::info('Disponibilidad de libro restaurada', [
                'book_id' => $bookId,
                'available_copies' => $book->available_physical_copies,
            ]);
        }
    }

    /**
     * Agregar una nota al campo notes de la reserva
     * 
     * @param string|null $existingNotes
     * @param string $newNote
     * @return string
     */
    private function appendNote(?string $existingNotes, string $newNote): string
    {
        if (empty($existingNotes)) {
            return $newNote;
        }

        return $existingNotes . "\n" . $newNote;
    }
}

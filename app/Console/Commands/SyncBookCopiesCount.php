<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;

class SyncBookCopiesCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:sync-copies-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar contadores de copias físicas con los registros reales en la base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sincronizando contadores de copias físicas...');
        
        $books = Book::whereIn('book_type', ['physical', 'both'])->get();
        
        $updated = 0;
        
        foreach ($books as $book) {
            $totalCopies = $book->physicalCopies()->count();
            $availableCopies = $book->physicalCopies()->where('status', 'available')->count();
            
            $book->update([
                'total_physical_copies' => $totalCopies,
                'available_physical_copies' => $availableCopies,
            ]);
            
            $this->line("✓ {$book->title}: {$totalCopies} copias totales, {$availableCopies} disponibles");
            $updated++;
        }
        
        $this->info("✓ {$updated} libros actualizados correctamente");
        
        return Command::SUCCESS;
    }
}

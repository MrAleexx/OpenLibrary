<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\UserDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    /**
     * Register and stream a book download
     */
    public function download(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $book = Book::findOrFail($request->book_id);

        // Validar que el libro sea descargable
        if (!$book->downloadable || !$book->pdf_file) {
            return response()->json([
                'error' => 'Este libro no está disponible para descarga.',
                'code' => 'NOT_DOWNLOADABLE'
            ], 422);
        }

        // Validar que el archivo existe
        if (!Storage::disk('public')->exists($book->pdf_file)) {
            return response()->json([
                'error' => 'El archivo PDF no se encuentra disponible.',
                'code' => 'FILE_NOT_FOUND'
            ], 404);
        }

        // Registrar la descarga usando transacción
        DB::transaction(function () use ($book, $request) {
            UserDownload::create([
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'downloaded_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Incrementar contador de descargas del libro
            $book->increment('total_downloads');
        });

        // Retornar respuesta exitosa (el frontend manejará la descarga directa)
        return response()->json([
            'success' => true,
            'message' => 'Descarga registrada exitosamente',
            'download_url' => $book->pdf_url,
        ]);
    }

    /**
     * Stream the PDF file for download
     */
    public function stream(Book $book): StreamedResponse
    {
        // Validar que el libro sea descargable
        if (!$book->downloadable || !$book->pdf_file) {
            abort(404, 'Libro no disponible para descarga');
        }

        // Validar que el archivo existe
        if (!Storage::disk('public')->exists($book->pdf_file)) {
            abort(404, 'Archivo no encontrado');
        }

        $filePath = Storage::disk('public')->path($book->pdf_file);
        $fileName = $book->title . '.pdf';

        return response()->streamDownload(function () use ($filePath) {
            echo file_get_contents($filePath);
        }, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}

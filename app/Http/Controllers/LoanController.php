<?php

namespace App\Http\Controllers;

use App\Models\BookLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoanController extends Controller
{
    /**
     * Display a listing of user's loans
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // Get active loans with relationships
        $activeLoans = BookLoan::with([
            'physicalCopy.book:id,title,cover_image',
            'physicalCopy:id,book_id,barcode'
        ])
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($loan) {
                return [
                    'id' => $loan->id,
                    'physical_copy' => [
                        'id' => $loan->physicalCopy->id,
                        'barcode' => $loan->physicalCopy->barcode,
                        'book' => [
                            'id' => $loan->physicalCopy->book->id,
                            'title' => $loan->physicalCopy->book->title,
                            'cover_image' => $loan->physicalCopy->book->cover_url ?? null,
                        ]
                    ],
                    'loan_date' => $loan->loan_date->toISOString(),
                    'due_date' => $loan->due_date->toISOString(),
                    'actual_return_date' => $loan->actual_return_date?->toISOString(),
                    'status' => $loan->status,
                    'renewal_count' => $loan->renewal_count,
                    'notes' => $loan->notes,
                ];
            });

        // Get loan history (returned loans)
        $loanHistory = BookLoan::with([
            'physicalCopy.book:id,title,cover_image',
            'physicalCopy:id,book_id,barcode'
        ])
            ->where('user_id', $user->id)
            ->where('status', 'returned')
            ->orderBy('actual_return_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($loan) {
                return [
                    'id' => $loan->id,
                    'physical_copy' => [
                        'id' => $loan->physicalCopy->id,
                        'barcode' => $loan->physicalCopy->barcode,
                        'book' => [
                            'id' => $loan->physicalCopy->book->id,
                            'title' => $loan->physicalCopy->book->title,
                            'cover_image' => $loan->physicalCopy->book->cover_url ?? null,
                        ]
                    ],
                    'loan_date' => $loan->loan_date->toISOString(),
                    'due_date' => $loan->due_date->toISOString(),
                    'actual_return_date' => $loan->actual_return_date?->toISOString(),
                    'status' => $loan->status,
                    'renewal_count' => $loan->renewal_count,
                    'notes' => $loan->notes,
                ];
            });

        // Calculate stats
        $stats = [
            'total_loans' => BookLoan::where('user_id', $user->id)->count(),
            'active_loans' => BookLoan::where('user_id', $user->id)
                ->whereIn('status', ['active', 'overdue'])
                ->count(),
            'overdue_loans' => BookLoan::where('user_id', $user->id)
                ->where('status', 'overdue')
                ->count(),
        ];

        return Inertia::render('Loans/Index', [
            'activeLoans' => $activeLoans,
            'loanHistory' => $loanHistory,
            'stats' => $stats,
        ]);
    }

    /**
     * Request renewal for a loan
     * (Optional feature - can be implemented later)
     */
    public function requestRenewal(BookLoan $loan, Request $request)
    {
        // Verify loan belongs to user
        if ($loan->user_id !== Auth::id()) {
            abort(403, 'No autorizado para renovar este préstamo');
        }

        // Check if loan can be renewed
        if ($loan->status === 'returned') {
            return back()->with('error', 'No se puede renovar un préstamo ya devuelto');
        }

        if ($loan->status === 'overdue') {
            return back()->with('error', 'No se puede renovar un préstamo vencido');
        }

        if ($loan->renewal_count >= 2) {
            return back()->with('error', 'Has alcanzado el límite máximo de renovaciones (2)');
        }

        // Update loan
        $loan->update([
            'due_date' => $loan->due_date->addDays(14),
            'renewal_count' => $loan->renewal_count + 1,
        ]);

        return back()->with('success', 'Préstamo renovado exitosamente. Nueva fecha de devolución: ' . $loan->due_date->format('d/m/Y'));
    }
}

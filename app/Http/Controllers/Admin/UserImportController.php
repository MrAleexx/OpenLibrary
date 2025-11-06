<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Carbon\Carbon;

class UserImportController extends Controller
{
    public function import()
    {
        return Inertia::render('admin/users/Import');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:10240'
        ]);

        try {
            DB::beginTransaction();

            $import = new UsersImport();
            Excel::import($import, $request->file('import_file'));

            DB::commit();

            $results = $this->prepareImportResults($import);

            return back()
                ->with('success', $this->getSuccessMessage($results))
                ->with('import_results', $results);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();

            $errors = $this->formatValidationErrors($e->failures());

            return back()
                ->with('error', 'Se encontraron errores de validación en el archivo.')
                ->with('import_errors', $errors);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }

    /**
     * Preparar resultados combinando failures y errores custom
     */
    private function prepareImportResults(UsersImport $import)
    {
        $failures = $import->failures();
        $customErrors = $import->getErrors();

        $allErrors = [];

        foreach ($failures as $failure) {
            $allErrors[] = [
                'row' => $failure->row(),
                'field' => $failure->attribute(),
                'error' => implode(', ', $failure->errors()),
                'value' => $failure->values()[$failure->attribute()] ?? 'N/A',
                'type' => 'validation'
            ];
        }

        foreach ($customErrors as $error) {
            $allErrors[] = [
                'row' => $error['row'],
                'field' => 'general',
                'error' => $error['error'],
                'value' => 'N/A',
                'type' => 'system'
            ];
        }

        return [
            'total_rows' => $import->getRowCount(),
            'imported' => $import->getSuccessCount(),
            'skipped' => count($allErrors),
            'errors' => $allErrors,
            'has_errors' => !empty($allErrors)
        ];
    }

    /**
     * Formatear errores de validación
     */
    private function formatValidationErrors($failures)
    {
        $errors = [];

        foreach ($failures as $failure) {
            $errors[] = [
                'row' => $failure->row(),
                'field' => $failure->attribute(),
                'error' => implode(', ', $failure->errors()),
                'value' => $failure->values()[$failure->attribute()] ?? 'N/A'
            ];
        }

        return $errors;
    }

    /**
     * Mensaje de éxito personalizado
     */
    private function getSuccessMessage($results)
    {
        $imported = $results['imported'];
        $skipped = $results['skipped'];

        if ($skipped > 0) {
            return "Importación completada parcialmente. {$imported} usuarios importados, {$skipped} filas omitidas debido a errores.";
        }

        return "¡Importación completada exitosamente! {$imported} usuarios importados correctamente.";
    }


    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $templateData = [
            [
                'name',
                'last_name',
                'email',
                'dni',
                'phone',
                'user_type',
                'institutional_email',
                'institutional_id',
                'membership_expires_at',
                'max_concurrent_loans',
                'can_download',
                'is_active'
            ],
            [
                'Juan',
                'Pérez',
                'juan.perez@email.com',
                '12345678',
                '987654321',
                'student',
                'juan.perez@institution.edu.pe',
                'STU001',
                '2025-12-31',
                '3',
                '1',
                '1'
            ],
            [
                'María',
                'Gómez',
                'maria.gomez@email.com',
                '87654321',
                '912345678',
                'teacher',
                'maria.gomez@institution.edu.pe',
                'TCH001',
                '2025-12-31',
                '5',
                '1',
                '1'
            ],
            [
                'Carlos',
                'López',
                'carlos.lopez@email.com',
                '11223344',
                '934567890',
                'external',
                '',
                'EXT001',
                '2024-06-30',
                '2',
                '1',
                '1'
            ],
            [
                'Ana',
                'Rodríguez',
                'ana.rodriguez@email.com',
                '55667788',
                '945678901',
                'staff',
                'ana.rodriguez@institution.edu.pe',
                'STA001',
                '',
                '8',
                '1',
                '1'
            ],
            [
                'Luis',
                'Martínez',
                'luis.martinez@email.com',
                '99887766',
                '956789012',
                'student',
                'luis.martinez@institution.edu.pe',
                'STU002',
                '2024-08-15',
                '3',
                '0',
                '1'
            ]
        ];

        $fileName = 'plantilla-importacion-usuarios-' . date('Y-m-d') . '.csv';

        return Response::stream(
            function () use ($templateData) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM para UTF-8

                foreach ($templateData as $row) {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ]
        );
    }

    /**
     * Download import report
     */
    public function downloadImportReport(Request $request) // ✅ Agregar Request $request
    {
        // ✅ Obtener resultados reales de la sesión
        $results = $request->session()->get('import_results');

        if (!$results || empty($results['errors'])) {
            $reportData = [
                ['Resultado', 'Detalle'],
                ['Estado', 'No hay errores para reportar'],
                ['Usuarios importados', $results['imported'] ?? 0],
                ['Filas omitidas', $results['skipped'] ?? 0],
            ];
        } else {
            $reportData = [
                ['Fila', 'Campo', 'Error', 'Valor', 'Tipo']
            ];

            foreach ($results['errors'] as $error) {
                $reportData[] = [
                    $error['row'],
                    $error['field'],
                    $error['error'],
                    $error['value'],
                    $error['type']
                ];
            }
        }

        $fileName = 'reporte-importacion-' . date('Y-m-d-H-i') . '.csv';

        return Response::stream(
            function () use ($reportData) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");

                foreach ($reportData as $row) {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }

    /**
     * Clear import session
     */
    public function clearImportSession(Request $request) // ✅ Agregar Request $request
    {
        $request->session()->forget(['import_results', 'import_errors']);

        return back()->with('success', 'Sesión de importación limpiada exitosamente.');
    }


    /**
     * Get import statistics
     */
    public function getImportStats()
    {
        $lastWeek = Carbon::now()->subWeek();

        $stats = [
            'total_imported' => User::where('created_by', auth()->id())
                ->where('created_at', '>=', $lastWeek)
                ->count(),
            'last_import_date' => User::where('created_by', auth()->id())
                ->max('created_at'),
            'imports_this_month' => User::where('created_by', auth()->id())
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        return response()->json($stats);
    }
}

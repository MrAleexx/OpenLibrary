<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

            // ✅ OBTENER CONTRASEÑAS TEMPORALES
            $tempPasswords = $import->getTempPasswords();

            // ✅ REDIRIGIR A CONTRASEÑAS TEMPORALES DE IMPORTACIÓN
            return redirect()->route('admin.users.import.passwords')
                ->with('temp_passwords', $tempPasswords)
                ->with('import_results', $results)
                ->with('success', $this->getSuccessMessage($results));
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
                'librarian',
                'ana.rodriguez@institution.edu.pe',
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
                fputs($handle, "\xEF\xBB\xBF");

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
    public function downloadImportReport(Request $request)
    {
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
    public function clearImportSession(Request $request)
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
            'total_imported' => User::where('created_by', Auth::id())
                ->where('created_at', '>=', $lastWeek)
                ->count(),
            'last_import_date' => User::where('created_by', Auth::id())
                ->max('created_at'),
            'imports_this_month' => User::where('created_by', Auth::id())
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * NUEVO: Descargar reporte de contraseñas temporales
     */
    public function downloadPasswordReport(Request $request)
    {
        $tempPasswords = $request->session()->get('temp_passwords', []);
        $tempPassword = $request->session()->get('temp_password');

        // MANEJAR TANTO IMPORTACIÓN COMO CREACIÓN INDIVIDUAL
        if (empty($tempPasswords) && !$tempPassword) {
            $reportData = [
                ['Estado', 'No hay contraseñas temporales para reportar'],
                ['Nota', 'Las contraseñas solo están disponibles inmediatamente después de la creación/importación'],
            ];
        } else {
            $reportData = [
                ['#', 'Nombre', 'Email', 'ID Institucional', 'Contraseña Temporal', 'Fecha Expiración']
            ];

            // AGREGAR CONTRASEÑAS DE IMPORTACIÓN
            foreach ($tempPasswords as $index => $password) {
                $reportData[] = [
                    $index + 1,
                    $password['name'],
                    $password['email'],
                    $password['institutional_id'],
                    $password['temp_password'],
                    now()->addDays(7)->format('Y-m-d')
                ];
            }

            // AGREGAR CONTRASEÑA INDIVIDUAL SI EXISTE
            if ($tempPassword) {
                $userCreated = $request->session()->get('user_created');
                $userReset = $request->session()->get('user_reset');

                $userData = $userCreated ?? $userReset;

                if ($userData) {
                    $reportData[] = [
                        count($tempPasswords) + 1,
                        $userData['name'],
                        $userData['email'],
                        $userData['institutional_id'],
                        $tempPassword,
                        now()->addDays(7)->format('Y-m-d')
                    ];
                }
            }
        }

        $fileName = 'contraseñas-temporales-' . date('Y-m-d-H-i') . '.csv';

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
     * NUEVO: Ver contraseñas temporales
     */
    public function showTempPasswords(Request $request)
    {
        $tempPasswords = $request->session()->get('temp_passwords', []);
        $importResults = $request->session()->get('import_results');

        // DETECTAR SI ES CREACIÓN INDIVIDUAL O RESET
        $tempPassword = $request->session()->get('temp_password');
        $userCreated = $request->session()->get('user_created');
        $userReset = $request->session()->get('user_reset');

        return Inertia::render('admin/users/TempPasswords', [
            'tempPasswords' => $tempPasswords,
            'importResults' => $importResults,
            'tempPassword' => $tempPassword,
            'userCreated' => $userCreated,
            'userReset' => $userReset,
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error')
        ]);
    }
}

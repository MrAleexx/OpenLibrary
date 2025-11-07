<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnFailure
{
    use SkipsFailures;

    private $rows = 0;
    private $successCount = 0;
    private $errors = [];
    private $tempPasswords = [];

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        ++$this->rows;

        try {
            $formattedData = $this->formatRowData($row);

            // ✅ SIEMPRE GENERAR ID AUTOMÁTICO (igual que en creación individual)
            $institutionalId = $this->generateInstitutionalId($formattedData['user_type']);

            $tempPassword = Str::random(12);

            $user = new User([
                'name' => $formattedData['name'],
                'last_name' => $formattedData['last_name'],
                'email' => $formattedData['email'],
                'dni' => $formattedData['dni'],
                'phone' => $formattedData['phone'],
                'user_type' => $formattedData['user_type'],

                // ✅ SIEMPRE USAR ID AUTOMÁTICO (ignorar institutional_id del Excel)
                'institutional_email' => $formattedData['institutional_email'],
                'institutional_id' => $institutionalId, // ← SIEMPRE automático

                'membership_expires_at' => $formattedData['membership_expires_at'],
                'max_concurrent_loans' => $formattedData['max_concurrent_loans'],
                'can_download' => $formattedData['can_download'],
                'is_active' => $formattedData['is_active'],

                'password' => Hash::make($tempPassword),
                'is_temp_password' => true,
                'temp_password_expires_at' => now()->addDays(7),
                'created_by' => Auth::id(),
                'downloads_today' => 0,
                'last_download_reset' => null,
                'email_verified_at' => now(),
            ]);

            $user->save();
            ++$this->successCount;

            $this->tempPasswords[] = [
                'row' => $this->rows,
                'email' => $user->email,
                'name' => $user->name . ' ' . $user->last_name,
                'temp_password' => $tempPassword,
                'user_id' => $user->id,
                'institutional_id' => $user->institutional_id
            ];

            return $user;
        } catch (\Exception $e) {
            $this->errors[] = [
                'row' => $this->rows + 1,
                'error' => 'Error del sistema: ' . $e->getMessage(),
                'data' => $formattedData ?? $row
            ];
            return null;
        }
    }

    /**
     * Generar ID institucional automático
     */
    private function generateInstitutionalId(string $userType): string
    {
        $prefix = $this->getPrefixForUserType($userType);
        $year = date('y');

        $lastUser = User::where('institutional_id', 'like', "{$prefix}{$year}%")
            ->orderBy('institutional_id', 'desc')
            ->first();

        if ($lastUser && preg_match('/' . $prefix . $year . '(\d+)/', $lastUser->institutional_id, $matches)) {
            $lastNumber = intval($matches[1]);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $sequential = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$year}{$sequential}";
    }

    private function getPrefixForUserType(string $userType): string
    {
        $prefixes = [
            'admin' => 'ADM',
            'librarian' => 'BIB',
            'student' => 'EST',
            'teacher' => 'DOC',
            'external' => 'EXT',
            'staff' => 'PER'
        ];

        return $prefixes[$userType] ?? 'USU';
    }

    /**
     * Formatear y limpiar datos de la fila
     */
    private function formatRowData(array $row)
    {
        return [
            'name' => trim($row['name'] ?? ''),
            'last_name' => trim($row['last_name'] ?? ''),
            'email' => trim($row['email'] ?? ''),
            'dni' => trim($row['dni'] ?? ''),
            'phone' => trim($row['phone'] ?? ''),
            'user_type' => $this->normalizeUserType($row['user_type'] ?? 'student'),
            'institutional_email' => !empty($row['institutional_email']) ? trim($row['institutional_email']) : null,
            // ✅ REMOVED: 'institutional_id' => ... (ya no se usa)
            'membership_expires_at' => $this->parseDate($row['membership_expires_at'] ?? null),
            'max_concurrent_loans' => $this->parseInteger($row['max_concurrent_loans'] ?? 3),
            'can_download' => $this->parseBoolean($row['can_download'] ?? true),
            'is_active' => $this->parseBoolean($row['is_active'] ?? true),
        ];
    }

    /**
     * Normalizar tipo de usuario
     */
    private function normalizeUserType($userType)
    {
        $userType = strtolower(trim($userType));

        $mapping = [
            'estudiante' => 'student',
            'docente' => 'teacher',
            'profesor' => 'teacher',
            'externo' => 'external',
            'staff' => 'staff',
            'personal' => 'staff',
        ];

        return $mapping[$userType] ?? $userType;
    }

    /**
     * Parsear fecha
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parsear entero
     */
    private function parseInteger($value)
    {
        $value = intval($value);
        return max(1, min(10, $value));
    }

    /**
     * Parsear booleano
     */
    private function parseBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['1', 'true', 'yes', 'si', 'sí', 'verdadero']);
    }

    /**
     * Reglas de validación - REMOVER institutional_id
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'dni' => 'required|string|size:8|unique:users,dni',
            'phone' => 'required|string|size:9',
            'user_type' => 'required|in:student,teacher,external,staff',
            'institutional_email' => 'nullable|email|unique:users,institutional_email',
            // ✅ REMOVED: 'institutional_id' => 'nullable|string|max:255',
            'membership_expires_at' => 'nullable|date|after:today',
            'max_concurrent_loans' => 'nullable|integer|min:1|max:10',
            'can_download' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Mensajes de validación personalizados
     */
    public function customValidationMessages()
    {
        return [
            'email.unique' => 'El email :input ya existe en el sistema.',
            'dni.unique' => 'El DNI :input ya existe en el sistema.',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'phone.size' => 'El teléfono debe tener exactamente 9 dígitos.',
            'user_type.in' => 'El tipo de usuario debe ser: student, teacher, external o staff.',
        ];
    }

    /**
     * Nombres de atributos personalizados
     */
    public function customValidationAttributes()
    {
        return [
            'name' => 'nombre',
            'last_name' => 'apellido',
            'email' => 'correo electrónico',
            'dni' => 'DNI',
            'phone' => 'teléfono',
            'user_type' => 'tipo de usuario',
            'institutional_email' => 'email institucional',
            // ✅ REMOVED: 'institutional_id' => 'código institucional',
        ];
    }

    /**
     * Procesamiento por lotes para mejor rendimiento
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Lectura por chunks para archivos grandes
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Métodos para obtener estadísticas
     */
    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFailures(): array
    {
        return $this->failures;
    }

    /**
     * NUEVO: Obtener contraseñas temporales generadas
     */
    public function getTempPasswords(): array
    {
        return $this->tempPasswords;
    }

    /**
     * NUEVO: Obtener resumen de importación con contraseñas
     */
    public function getImportSummary(): array
    {
        return [
            'total_rows' => $this->rows,
            'imported' => $this->successCount,
            'temp_passwords' => $this->tempPasswords,
            'errors' => $this->errors,
            'failures' => $this->failures
        ];
    }
}

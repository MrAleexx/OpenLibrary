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
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
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

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        ++$this->rows;

        // Limpiar y formatear datos
        $formattedData = $this->formatRowData($row);

        try {
            $user = new User([
                'name' => $formattedData['name'],
                'last_name' => $formattedData['last_name'],
                'email' => $formattedData['email'],
                'dni' => $formattedData['dni'],
                'phone' => $formattedData['phone'],
                'user_type' => $formattedData['user_type'],
                'institutional_email' => $formattedData['institutional_email'],
                'institutional_id' => $formattedData['institutional_id'],
                'membership_expires_at' => $formattedData['membership_expires_at'],
                'max_concurrent_loans' => $formattedData['max_concurrent_loans'],
                'can_download' => $formattedData['can_download'],
                'is_active' => $formattedData['is_active'],
                'password' => Hash::make(Str::random(12)),
                'is_temp_password' => true,
                'temp_password_expires_at' => now()->addDays(7),
                'created_by' => auth()->id(),
            ]);

            $user->save();
            ++$this->successCount;

            return $user;
        } catch (\Exception $e) {
            $this->errors[] = [
                'row' => $this->rows + 1, // +1 por el header
                'error' => $e->getMessage(),
                'data' => $formattedData
            ];
            return null;
        }
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
            'institutional_id' => !empty($row['institutional_id']) ? trim($row['institutional_id']) : null,
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
        return max(1, min(10, $value)); // Limitar entre 1-10
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
     * Reglas de validación
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
            'institutional_id' => 'nullable|string|max:255',
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
            'institutional_id' => 'código institucional',
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
}

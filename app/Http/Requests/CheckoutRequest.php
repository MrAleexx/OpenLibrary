<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated and email verified
        return auth()->check() && auth()->user()->hasVerifiedEmail();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'book_ids' => [
                'required',
                'array',
                'min:1',
                'max:5',
            ],
            'book_ids.*' => [
                'required',
                'integer',
                'exists:books,id',
            ],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'book_ids.required' => 'Debes seleccionar al menos un libro',
            'book_ids.array' => 'El formato de los libros es inválido',
            'book_ids.min' => 'Debes seleccionar al menos 1 libro',
            'book_ids.max' => 'No puedes seleccionar más de 5 libros a la vez',
            'book_ids.*.required' => 'ID de libro requerido',
            'book_ids.*.integer' => 'ID de libro debe ser un número',
            'book_ids.*.exists' => 'Uno o más libros seleccionados no existen',
        ];
    }

    /**
     * Configure the validator instance with additional rules
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!$validator->errors()->any()) {
                $this->validateBooksAvailability($validator);
                $this->validateNoDuplicates($validator);
            }
        });
    }

    /**
     * Validate that all books are active and available
     */
    protected function validateBooksAvailability(Validator $validator): void
    {
        $bookIds = $this->input('book_ids', []);
        
        $books = Book::whereIn('id', $bookIds)->get();

        foreach ($books as $book) {
            // Check if book is active
            if (!$book->is_active) {
                $validator->errors()->add(
                    'book_ids',
                    "El libro '{$book->title}' no está disponible actualmente"
                );
            }

            // Check if book has available copies
            $hasAvailableCopies = $book->physicalCopies()
                ->where('status', 'available')
                ->exists();

            if (!$hasAvailableCopies) {
                $validator->errors()->add(
                    'book_ids',
                    "No hay copias disponibles de '{$book->title}'"
                );
            }

            // Check if user already has this book on loan
            $hasActiveLoan = auth()->user()->bookLoans()
                ->whereHas('physicalCopy', function ($query) use ($book) {
                    $query->where('book_id', $book->id);
                })
                ->where('status', 'active')
                ->exists();

            if ($hasActiveLoan) {
                $validator->errors()->add(
                    'book_ids',
                    "Ya tienes un préstamo activo de '{$book->title}'"
                );
            }
        }
    }

    /**
     * Validate no duplicate book IDs
     */
    protected function validateNoDuplicates(Validator $validator): void
    {
        $bookIds = $this->input('book_ids', []);
        
        if (count($bookIds) !== count(array_unique($bookIds))) {
            $validator->errors()->add(
                'book_ids',
                'No puedes seleccionar el mismo libro múltiples veces'
            );
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'book_ids' => 'libros',
            'book_ids.*' => 'libro',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        if (!auth()->check()) {
            abort(401, 'Debes iniciar sesión para realizar préstamos');
        }

        if (!auth()->user()->hasVerifiedEmail()) {
            abort(403, 'Debes verificar tu correo electrónico antes de realizar préstamos');
        }

        parent::failedAuthorization();
    }
}

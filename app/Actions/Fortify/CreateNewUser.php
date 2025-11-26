<?php
// app/Actions/Fortify/CreateNewUser.php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'dni' => ['required', 'string', 'size:8', Rule::unique(User::class)],
            'phone' => ['required', 'string', 'size:9', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'dni' => $input['dni'],
            'phone' => $input['phone'],
            'password' => $input['password'],
            'user_type' => 'external', // Por defecto para registros públicos
            'institutional_id' => 'EXT-' . strtoupper(uniqid()),
            'membership_expires_at' => now()->addYear(),
            'max_concurrent_loans' => 3,
            'can_download' => true,
            'is_temp_password' => false,
            'is_active' => false,
        ]);

        // Asignar rol de usuario por defecto
        $user->assignRole('user');

        return $user;
    }
}
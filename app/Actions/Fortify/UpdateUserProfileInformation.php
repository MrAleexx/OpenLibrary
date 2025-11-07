<?php
// app/Actions/Fortify/UpdateUserProfileInformation.php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'dni' => [
                'required',
                'string',
                'size:8',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => [
                'required',
                'string',
                'size:9',
                Rule::unique('users')->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'dni' => $input['dni'],
                'phone' => $input['phone'],
            ])->save();
        }
    }

    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'dni' => $input['dni'],
            'phone' => $input['phone'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}

<?php

namespace App\Actions;

use App\Models\User;

class RegisterUserAction
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     * @return array{user: User, token: string}
     */
    public function execute(array $data, string $deviceName = 'mobile'): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->assignRole('user');

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user->load('roles:id,name'),
            'token' => $token,
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $user = User::firstOrCreate(
            ['email' => env('ANIMOJI_ADMIN_EMAIL', 'admin@animoji.local')],
            [
                'name' => env('ANIMOJI_ADMIN_NAME', 'Administrador ANIMOJI'),
                'password' => Hash::make(env('ANIMOJI_ADMIN_PASSWORD', 'Admin123!')),
            ],
        );

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->syncRoles(['admin']);

        $this->command?->info('Admin local: '.$user->email);
    }
}

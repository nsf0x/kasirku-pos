<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->where('role', 'owner')->exists()) {
            return;
        }

        $name = trim((string) env('BOOTSTRAP_OWNER_NAME'));
        $email = strtolower(trim((string) env('BOOTSTRAP_OWNER_EMAIL')));
        $password = (string) env('BOOTSTRAP_OWNER_PASSWORD');

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            if (app()->environment('production')) {
                throw new RuntimeException(
                    'Set BOOTSTRAP_OWNER_NAME, BOOTSTRAP_OWNER_EMAIL, and a BOOTSTRAP_OWNER_PASSWORD of at least 12 characters before seeding.'
                );
            }

            $this->command?->warn('Owner was not created. Configure BOOTSTRAP_OWNER_* values before running db:seed.');

            return;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->command?->info('Initial owner account created.');
    }
}

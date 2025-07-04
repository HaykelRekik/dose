<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()
            ->firstOrCreate([
                'email' => 'admin@dose.com',
            ], [
                'name' => 'admin',
                'password' => '123123123',
                'role' => UserRole::ADMIN,
            ]);
    }
}

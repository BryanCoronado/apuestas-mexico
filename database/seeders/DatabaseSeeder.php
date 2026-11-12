<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = config('ruleta.admin.password');

        if (! $password) {
            $password = Str::password(16);
            $this->command?->warn("PANEL_ADMIN_PASSWORD no definido. Contraseña generada: {$password}");
        }

        User::updateOrCreate(
            ['email' => config('ruleta.admin.email')],
            ['name' => config('ruleta.admin.name'), 'password' => $password],
        );

        $this->call(RifaSeeder::class);
    }
}

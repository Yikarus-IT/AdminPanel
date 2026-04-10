<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->delete();

        User::factory()->createMany([
            [
                'name' => 'Luis Ponce',
                'email' => 'luis@adminpanel.test',
                'password' => 'password',
            ],
            [
                'name' => 'Andrea Ruiz',
                'email' => 'andrea@adminpanel.test',
                'password' => 'password',
            ],
            [
                'name' => 'Marco Vega',
                'email' => 'marco@adminpanel.test',
                'password' => 'password',
            ],
            [
                'name' => 'Sofia Mendez',
                'email' => 'sofia@adminpanel.test',
                'password' => 'password',
            ],
        ]);
    }
}

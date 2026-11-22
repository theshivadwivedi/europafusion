<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the website content. Admin users are not seeded on purpose:
     * create one with `php artisan admin:create`.
     */
    public function run(): void
    {
        $this->call(ContentSeeder::class);
    }
}

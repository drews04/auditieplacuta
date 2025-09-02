<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(ForumSeeder::class);
        $this->call(ForumTestDataSeeder::class);
        // EventsSeeder removed - no auto-generated events
    }
}

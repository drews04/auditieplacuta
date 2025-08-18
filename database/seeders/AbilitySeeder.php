<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AbilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        $now = Carbon::now();

        DB::table('abilities')->insert([
            [
                'name' => 'Fură vot',
                'code' => 'steal',
                'description' => 'Fură un vot de la un alt utilizator și adaugă-l la melodia ta.',
                'cooldown' => 48,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Scut',
                'code' => 'shield',
                'description' => 'Protejează melodia ta de voturi furate timp de 24h.',
                'cooldown' => 24,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Îngheață',
                'code' => 'freeze',
                'description' => 'Oprește un alt utilizator din a vota pentru o rundă.',
                'cooldown' => 72,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Vot suplimentar',
                'code' => 'extra_vote',
                'description' => 'Îți oferă un vot suplimentar în concursul curent.',
                'cooldown' => 24,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Blocare',
                'code' => 'block',
                'description' => 'Blochează o abilitate să te afecteze timp de o zi.',
                'cooldown' => 48,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Dezvăluie voturi',
                'code' => 'reveal',
                'description' => 'Vezi cine a votat ce în ultima rundă.',
                'cooldown' => 96,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
}
}
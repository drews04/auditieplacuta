<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThemePool;

class ThemePoolSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            // CSD (Cu și Despre)
            ['category' => 'csd', 'name' => 'Dragoste'],
            ['category' => 'csd', 'name' => 'Despărțiri'],
            ['category' => 'csd', 'name' => 'Prietenie'],
            ['category' => 'csd', 'name' => 'Vară'],
            ['category' => 'csd', 'name' => 'Iarnă'],
            ['category' => 'csd', 'name' => 'Bani'],
            ['category' => 'csd', 'name' => 'Petrecere'],
            ['category' => 'csd', 'name' => 'Dans'],
            ['category' => 'csd', 'name' => 'Noapte'],
            ['category' => 'csd', 'name' => 'Vacanță'],

            // IT (în titlu cuvânt / entități / locuri / ani)
            ['category' => 'it', 'name' => 'Boybands'],
            ['category' => 'it', 'name' => 'Girlbands'],
            ['category' => 'it', 'name' => 'Michael Jackson'],
            ['category' => 'it', 'name' => 'ABBA'],
            ['category' => 'it', 'name' => '80s'],
            ['category' => 'it', 'name' => '90s'],
            ['category' => 'it', 'name' => '2000s'],
            ['category' => 'it', 'name' => 'România'],
            ['category' => 'it', 'name' => 'Londra'],
            ['category' => 'it', 'name' => 'New York'],

            // ARTIȘTI
            ['category' => 'artisti', 'name' => 'Queen'],
            ['category' => 'artisti', 'name' => 'The Beatles'],
            ['category' => 'artisti', 'name' => 'Madonna'],
            ['category' => 'artisti', 'name' => 'Beyoncé'],
            ['category' => 'artisti', 'name' => 'Eminem'],
            ['category' => 'artisti', 'name' => 'Drake'],
            ['category' => 'artisti', 'name' => 'Taylor Swift'],
            ['category' => 'artisti', 'name' => 'Smiley'],
            ['category' => 'artisti', 'name' => 'Delia'],
            ['category' => 'artisti', 'name' => 'Andra'],

            // GENURI
            ['category' => 'genuri', 'name' => 'Pop'],
            ['category' => 'genuri', 'name' => 'Rock'],
            ['category' => 'genuri', 'name' => 'Hip-Hop'],
            ['category' => 'genuri', 'name' => 'Disco'],
            ['category' => 'genuri', 'name' => 'Jazz'],
            ['category' => 'genuri', 'name' => 'Blues'],
            ['category' => 'genuri', 'name' => 'Dance'],
            ['category' => 'genuri', 'name' => 'Folk'],
            ['category' => 'genuri', 'name' => 'Latino'],
            ['category' => 'genuri', 'name' => 'Manele'],
        ];

        ThemePool::query()->insert($themes);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Forum\Category;

class ForumSeeder extends Seeder
{
    public function run()
    {
        // Final set (no “Anunțuri”, no “Tutoriale & Resurse”)
        $categories = [
            ['name' => 'Concurs',                'description' => 'Discuții despre concursuri și teme'],
            ['name' => 'General',                'description' => 'Discuții generale despre muzică și alte subiecte'],
            ['name' => 'Muzică',                 'description' => 'Totul despre muzică'],
            ['name' => 'Meme',                   'description' => 'Meme-uri și fun'],
            ['name' => 'Întrebări & Ajutor',     'description' => 'Întrebări, ajutor, probleme tehnice'],
            ['name' => 'Feedback & Bug-uri',     'description' => 'Sugestii, feedback și raportare bug-uri'],
            ['name' => 'Off-topic',              'description' => 'Discuții off-topic'],
            ['name' => 'Evenimente & Întâlniri', 'description' => 'Întâlniri, evenimente și anunțuri sociale'],
        ];

        foreach ($categories as $cat) {
            $name = $cat['name'];
            $slug = Str::slug($name, '-'); // diacritics-safe slug
            $desc = isset($cat['description']) ? $cat['description'] : null;

            // idempotent (re-running won’t duplicate)
            Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'slug' => $slug, 'description' => $desc]
            );
        }
    }
}

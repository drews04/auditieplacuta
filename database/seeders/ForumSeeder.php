<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Forum\Category;
use Illuminate\Support\Str;

class ForumSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Anunțuri',
                'slug' => 'anunturi',
                'description' => 'Anunțuri importante despre site și concursuri'
            ],
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'Discuții generale despre muzică și alte subiecte'
            ],
            [
                'name' => 'Concurs',
                'slug' => 'concurs',
                'description' => 'Discuții despre concursuri și teme'
            ],
            [
                'name' => 'Feedback',
                'slug' => 'feedback',
                'description' => 'Sugestii și feedback pentru site'
            ],
            [
                'name' => 'Offtopic',
                'slug' => 'offtopic',
                'description' => 'Discuții off-topic și amuzante'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

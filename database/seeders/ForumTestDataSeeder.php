<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Forum\Category;
use App\Models\Forum\Thread;
use App\Models\User;

class ForumTestDataSeeder extends Seeder
{
    public function run()
    {
        // Get or create a test user
        $user = User::first() ?? User::factory()->create();
        
        // Get categories
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->call(ForumSeeder::class);
            $categories = Category::all();
        }
        
        // Create some test threads
        $threads = [
            [
                'category' => 'Concurs',
                'title' => 'Cum să câștig mai multe puncte în concurs?',
                'body' => 'Vă rog să îmi spuneți ce strategii folosiți pentru a obține puncte maxime în concursul zilnic. Am observat că unii jucători obțin rezultate foarte bune și aș vrea să învăț de la ei.',
            ],
            [
                'category' => 'Anunțuri',
                'title' => 'Noua temă lunară - Rock Alternativ',
                'body' => 'Excelentă alegere pentru luna aceasta! Rock alternativul are o energie incredibilă și sunt sigur că va inspira multe melodii grozave. Să vedem ce vor crea jucătorii noștri!',
            ],
            [
                'category' => 'General',
                'title' => 'Recomandări pentru playlist-uri de dimineață',
                'body' => 'Căutați muzică energizantă pentru începutul zilei? Împărtășiți-vă preferințele și să descoperim împreună melodii noi care să ne dea energia necesară pentru o zi productivă.',
            ],
            [
                'category' => 'Feedback',
                'title' => 'Probleme cu încărcarea melodiilor',
                'body' => 'Am încercat să încarc o melodie dar primesc eroarea "File too large". Cineva poate să mă ajute să rezolv această problemă? Melodia mea are doar 5MB.',
            ],
            [
                'category' => 'General',
                'title' => 'Cel mai bun artist din anul 2024?',
                'body' => 'Să discutăm despre artiștii care au lansat cele mai bune piese în acest an. Care credeți că merită titlul de artist al anului și de ce?',
            ],
            [
                'category' => 'Offtopic',
                'title' => 'Memes muzicale - să râdem puțin!',
                'body' => 'Aduceți-vă cele mai amuzante meme-uri legate de muzică. Să ne distrăm puțin și să râdem împreună!',
            ]
        ];
        
        foreach ($threads as $threadData) {
            $category = $categories->where('name', $threadData['category'])->first();
            
            if ($category) {
                Thread::create([
                    'category_id' => $category->id,
                    'user_id' => $user->id,
                    'title' => $threadData['title'],
                    'slug' => \Illuminate\Support\Str::slug($threadData['title']) . '-' . \Illuminate\Support\Str::random(6),
                    'body' => $threadData['body'],
                    'last_posted_at' => now(),
                    'last_post_user_id' => $user->id,
                ]);
                
                // Update category thread count
                $category->increment('threads_count');
            }
        }
    }
}

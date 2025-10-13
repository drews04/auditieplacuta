<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Release;
use App\Models\Artist;
use App\Models\ReleaseCategory;

class ReleaseAdminController extends Controller
{
    public function create()
    {
        return view('releases.create'); // your form lives in resources/views/releases/create.blade.php
    }

    public function store(Request $request)
    {
        // 1) Validate input
        $data = $request->validate([
            'title'         => ['required','string','max:200'],
            'release_date'  => ['required','date'],
            'type'          => ['nullable','string','max:50'],
            'description'   => ['nullable','string','max:5000'],
            'is_highlight'  => ['nullable','boolean'],
            'artists'       => ['nullable','string','max:500'],   // "A, B, C"
            'categories'    => ['nullable','string','max:500'],   // "Pop, Rock"
            'cover'         => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        // 2) Keep the real date, but FORCE display week to the CURRENT ISO week
        $date    = Carbon::parse($data['release_date']);
        $weekKey = now()->format('o\WW'); // e.g. 2025W39

        // 3) Cover upload (public disk)
        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers/releases', 'public');
        }

        // 4) Create release
        $release = Release::create([
            'title'        => $data['title'],
            'slug'         => Str::slug($data['title'].'-'.now()->format('His')),
            'release_date' => $date,
            'week_key'     => $weekKey,     // show in THIS week
            'type'         => $data['type'] ?? null,
            'cover_path'   => $coverPath,
            'description'  => $data['description'] ?? null,
            'is_highlight' => (bool)($data['is_highlight'] ?? false),
        ]);

        // 5) Attach artists
        $artistNames = collect(explode(',', (string)($data['artists'] ?? '')))
            ->map(fn($s) => trim($s))
            ->filter()
            ->unique();

        if ($artistNames->isNotEmpty()) {
            $artistIds = $artistNames->map(fn($name) =>
                Artist::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id
            );
            $release->artists()->sync($artistIds);
        }

        // 6) Attach categories
        $catNames = collect(explode(',', (string)($data['categories'] ?? '')))
            ->map(fn($s) => trim($s))
            ->filter()
            ->unique();

        if ($catNames->isNotEmpty()) {
            $catIds = $catNames->map(fn($name) =>
                ReleaseCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id
            );
            $release->categories()->sync($catIds);
        }

        // 7) Ensure a single Hero per week
        if ($release->is_highlight) {
            Release::where('week_key', $weekKey)
                ->where('id', '!=', $release->id)
                ->update(['is_highlight' => false]);
        }

        // 8) Done
        return redirect()
            ->route('releases.week', $weekKey)
            ->with('success', 'Lansarea a fost adăugată în săptămâna curentă.');
    }
}

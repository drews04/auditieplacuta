<?php

namespace App\Http\Controllers;

use App\Models\Release;
use Illuminate\Http\Request;

class NoutatiInMuzicaController extends Controller
{
    public function index(Request $request)
{
    // Always land on the CURRENT ISO week page
    $weekKey = now()->format('o\WW'); // e.g. 2025W39
    return $this->week($request, $weekKey);
}
    
    
    public function week(Request $request, string $week_key)
    {
        $query = \App\Models\Release::with(['artists','categories'])
            ->where('week_key', $week_key);
    
        // (filters come later) — keep it clean now
    
        $releases = $query->orderByDesc('is_highlight')->orderBy('title')->get();
        $hero = $releases->firstWhere('is_highlight', true) ?? $releases->first();
    
        $prevWeek = \App\Models\Release::where('week_key','<',$week_key)->orderByDesc('week_key')->value('week_key');
        $nextWeek = \App\Models\Release::where('week_key','>',$week_key)->orderBy('week_key')->value('week_key');
    
        return view('releases.index', [
            'weekKey'   => $week_key,
            'hero'      => $hero,
            'releases'  => $releases->reject(fn($r) => $r->id === optional($hero)->id),
            'prevWeek'  => $prevWeek,
            'nextWeek'  => $nextWeek,
        ]);
    }
    
    public function show(string $slug)
    {
        $release = \App\Models\Release::with(['artists','categories'])
            ->where('slug',$slug)->firstOrFail();
    
        return view('releases.show', compact('release'));
    }
    
}

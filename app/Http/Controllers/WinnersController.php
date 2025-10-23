<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Winner;

class WinnersController extends Controller
{
    public function index(Request $request)
    {
        $q   = trim((string) $request->input('q', ''));
        $per = (int) $request->input('per', 20);
        if ($per <= 0 || $per > 100) $per = 20;

        $winners = Winner::query()
            ->with([
                'song:id,title,youtube_url',
                'user:id,name',
                'cycle:id,theme_text,vote_end_at',
                'theme:id,name' // ok if null; blade guards it
            ])
            ->when($q !== '', function ($qry) use ($q) {
                $qry->where(function ($qq) use ($q) {
                    $qq->whereHas('song', fn($s) => $s->where('title', 'like', "%{$q}%"))
                       ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"))
                       ->orWhereHas('cycle', fn($c) => $c->where('theme_text', 'like', "%{$q}%"))
                       ->orWhereHas('theme', fn($t) => $t->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('contest_date')
            ->orderByDesc('id')
            ->paginate($per)
            ->withQueryString();

        return view('concurs.winners', [
            'winners' => $winners,
            'q'       => $q,
            'per'     => $per,
        ]);
    }
}

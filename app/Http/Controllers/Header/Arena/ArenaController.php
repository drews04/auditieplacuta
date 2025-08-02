<?php

namespace App\Http\Controllers\Header\Arena;

use App\Http\Controllers\Controller;

class ArenaController extends Controller
{
    public function index()
    {
        return view('arena.arena');
    }
}

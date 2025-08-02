<?php

namespace App\Http\Controllers\Header\Arena;

use App\Http\Controllers\Controller;

class AbilitatiDisponibileController extends Controller
{
    public function index()
    {
        return view('arena.abilitati-disponibile');
    }
}

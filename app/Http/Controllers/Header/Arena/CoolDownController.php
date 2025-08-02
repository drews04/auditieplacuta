<?php

namespace App\Http\Controllers\Header\Arena;

use App\Http\Controllers\Controller;

class CooldownController extends Controller
{
    public function index()
    {
        return view('arena.cooldown');
    }
}
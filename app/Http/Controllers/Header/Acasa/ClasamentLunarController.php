<?php

namespace App\Http\Controllers\Header\Acasa;

use App\Http\Controllers\Controller;

class ClasamentLunarController extends Controller
{
    public function index()
    {
        return view('acasa.clasament-lunar');
    }
}
<?php

namespace App\Http\Controllers\Header\Acasa;

use App\Http\Controllers\Controller;

class RegulamentController extends Controller
{
    public function index()
    {
        return view('acasa.regulament');
    }
}

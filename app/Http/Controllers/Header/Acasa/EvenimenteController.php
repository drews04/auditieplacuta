<?php

namespace App\Http\Controllers\Header\Acasa;

use App\Http\Controllers\Controller;

class EvenimenteController extends Controller
{
    public function index()
    {
        return view('acasa.evenimente');
    }
}

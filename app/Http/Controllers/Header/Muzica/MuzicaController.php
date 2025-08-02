<?php

namespace App\Http\Controllers\Header\Muzica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MuzicaController extends Controller
{
    public function index()
    {
        return view('muzica.muzica');
    }
}

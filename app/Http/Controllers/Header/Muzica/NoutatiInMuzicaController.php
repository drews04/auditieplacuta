<?php

namespace App\Http\Controllers\Header\Muzica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NoutatiInMuzicaController extends Controller
{
    public function index()
    {
        return view('muzica.noutati-in-muzica');
    }
}
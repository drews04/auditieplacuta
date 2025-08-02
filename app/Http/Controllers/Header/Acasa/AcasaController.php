<?php

namespace App\Http\Controllers\Header\Acasa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AcasaController extends Controller
{
    public function index()
    {
        return view('home');
    }
}

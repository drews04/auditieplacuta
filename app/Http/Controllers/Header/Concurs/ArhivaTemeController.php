<?php

namespace App\Http\Controllers\Header\Concurs;

use App\Http\Controllers\Controller;

class ArhivaTemeController extends Controller
{
    public function index()
    {
        return view('concurs.arhiva-teme');
    }
}
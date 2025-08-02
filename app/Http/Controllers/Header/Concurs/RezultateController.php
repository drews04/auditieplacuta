<?php

namespace App\Http\Controllers\Header\Concurs;

use App\Http\Controllers\Controller;

class RezultateController extends Controller
{
    public function index()
    {
        return view('concurs.rezultate');
    }
}
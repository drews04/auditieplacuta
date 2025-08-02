<?php

namespace App\Http\Controllers\Header\Concurs;

use App\Http\Controllers\Controller;

class IncarcaMelodieController extends Controller
{
    public function index()
    {
        return view('concurs.incarca-melodie');
    }
}

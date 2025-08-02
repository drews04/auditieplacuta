<?php

namespace App\Http\Controllers\Header\Concurs;

use App\Http\Controllers\Controller;

class MelodiileZileiController extends Controller
{
    public function index()
    {
        return view('concurs.melodiile-zilei');
    }
}
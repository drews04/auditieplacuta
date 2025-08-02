<?php

namespace App\Http\Controllers\Header\Concurs;

use App\Http\Controllers\Controller;

class ConcursController extends Controller
{
    public function index()
    {
        return view('concurs.concurs');
    }
}
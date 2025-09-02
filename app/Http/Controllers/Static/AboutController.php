<?php

namespace App\Http\Controllers\Static;

use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    public function index()
    {
        return view('pages.about');
    }
}

<?php

namespace App\Http\Controllers\Header\Misiuni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GhicesteMelodiaController extends Controller
{
    public function index()
    {
        return view ('misiuni.ghiceste-melodia');
    }
}

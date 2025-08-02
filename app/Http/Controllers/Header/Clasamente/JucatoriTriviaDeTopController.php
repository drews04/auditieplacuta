<?php

namespace App\Http\Controllers\Header\Clasamente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JucatoriTriviaDeTopController extends Controller
{
     public function index()
    {
        return view ('clasamente.jucatori-trivia-de-top');
    }
}

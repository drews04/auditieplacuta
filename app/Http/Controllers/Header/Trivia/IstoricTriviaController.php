<?php

namespace App\Http\Controllers\Header\Trivia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class IstoricTriviaController extends Controller
{
    public function index()
    {
        return view('trivia.istoric-trivia');
    }
}

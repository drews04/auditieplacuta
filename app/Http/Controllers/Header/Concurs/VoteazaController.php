<?php

namespace App\Http\Controllers\Header\Concurs;

use App\Http\Controllers\Controller;

class VoteazaController extends Controller
{
    public function index()
    {
        return view('concurs.voteaza');
    }
}
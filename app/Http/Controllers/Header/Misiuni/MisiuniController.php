<?php

namespace App\Http\Controllers\Header\Misiuni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MisiuniController extends Controller
{
    public function index()
    {
        return view ('misiuni.misiuni');
    }
}

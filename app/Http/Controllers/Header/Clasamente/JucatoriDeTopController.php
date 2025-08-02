<?php

namespace App\Http\Controllers\Header\Clasamente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JucatoriDeTopController extends Controller
{
     public function index()
    {
        return view ('clasamente.jucatori-de-top');
    }
}

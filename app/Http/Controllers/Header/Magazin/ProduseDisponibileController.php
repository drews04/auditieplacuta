<?php

namespace App\Http\Controllers\Header\Magazin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProduseDisponibileController extends Controller
{
    public function index()
    {
        return ('magazin.produse-disponibile');
    }
}

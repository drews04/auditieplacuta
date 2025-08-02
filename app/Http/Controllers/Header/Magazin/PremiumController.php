<?php

namespace App\Http\Controllers\Header\Magazin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PremiumController extends Controller
{
    public function index()
    {
        return view ('magazin.premium');
    }
}

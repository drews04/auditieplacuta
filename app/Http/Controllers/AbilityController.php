<?php

namespace App\Http\Controllers;

use App\Models\Ability;
use Illuminate\Http\Request;

class AbilityController extends Controller
{
    public function index()
    {
        $abilities = Ability::all();
        return view('user.abilities', compact('abilities'));
    }
}
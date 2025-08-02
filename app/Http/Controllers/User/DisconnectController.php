<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DisconnectController extends Controller
{
    public function index(){
        return view ('user.disconnect');
    }
}

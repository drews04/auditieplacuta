<?php

namespace App\Http\Controllers\Header\Muzica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlaylistsController extends Controller
{
    public function index()
    {
        return view('muzica.playlists');
    }
}
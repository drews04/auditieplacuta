<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forum\Category;
use App\Models\Forum\Thread;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        
        $threads = Thread::with(['category', 'user'])
            ->visible()
            ->orderByDesc('pinned')
            ->orderByDesc('updated_at')
            ->paginate(10);
        
        return view('forum.home', compact('categories', 'threads'));
    }
}

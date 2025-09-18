<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ContestCycle;

class ConcursPosterController extends Controller
{
    public function store(Request $request)
    {
        // Validate: must have a cycle and an image up to 15 MB
        $data = $request->validate([
            'cycle_id' => ['required', 'integer', 'exists:contest_cycles,id'],
            'poster'   => ['required', 'image', 'mimes:webp,jpg,jpeg,png,avif', 'max:15360'], // 15 MB
        ]);
    
        $cycle = \App\Models\ContestCycle::findOrFail($data['cycle_id']);
    
        $file = $request->file('poster');
        $ext  = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'webp');
        $name = "cycle_{$cycle->id}.{$ext}";
    
        // If there was a previous poster, try to delete it (any ext saved as URL)
        if (!empty($cycle->poster_url)) {
            $oldUrlPath = parse_url($cycle->poster_url, PHP_URL_PATH) ?: '';
            // convert "/storage/concurs_posters/..." -> "concurs_posters/..."
            $oldStoragePath = ltrim(str_replace('/storage/', '', $oldUrlPath), '/');
            if ($oldStoragePath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldStoragePath);
            }
        }
    
       // Save to storage/app/public/concurs_posters/<name>
$storedPath = $file->storeAs('concurs_posters', $name, 'public');

// Write a FULL absolute URL (avoids any base-path quirks)
$cycle->poster_url = asset('storage/'.$storedPath);   // e.g. http://127.0.0.1:8000/storage/concurs_posters/cycle_15.png
$cycle->save();

    
        return back()->with('status', 'Poster actualizat.');
    }

    public function destroy(Request $request)
{
    $data = $request->validate([
        'cycle_id' => ['required','integer','exists:contest_cycles,id'],
    ]);

    $cycle = \App\Models\ContestCycle::findOrFail($data['cycle_id']);

    // delete file on disk if we have one
    if (!empty($cycle->poster_url)) {
        $oldUrlPath = parse_url($cycle->poster_url, PHP_URL_PATH) ?: '';
        $oldStoragePath = ltrim(str_replace('/storage/', '', $oldUrlPath), '/'); // -> public disk path
        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldStoragePath);
    }

    $cycle->poster_url = null;
    $cycle->save();

    return back()->with('status', 'Poster eliminat.');
}

    
}

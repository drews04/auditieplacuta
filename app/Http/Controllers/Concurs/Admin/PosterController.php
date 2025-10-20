<?php

namespace App\Http\Controllers\Concurs\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ContestCycle;

/**
 * Admin poster management for contest cycles
 */
class PosterController extends Controller
{
    /**
     * Upload a new poster for a cycle
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cycle_id' => ['required', 'integer', 'exists:contest_cycles,id'],
            'poster'   => ['required', 'image', 'mimes:webp,jpg,jpeg,png,avif', 'max:15360'], // 15 MB
        ]);

        $cycle = ContestCycle::findOrFail($data['cycle_id']);

        $file = $request->file('poster');
        $ext  = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'webp');
        $name = "cycle_{$cycle->id}.{$ext}";

        // Delete previous poster if exists
        if (!empty($cycle->poster_url)) {
            $oldUrlPath = parse_url($cycle->poster_url, PHP_URL_PATH) ?: '';
            $oldStoragePath = ltrim(str_replace('/storage/', '', $oldUrlPath), '/');
            if ($oldStoragePath) {
                Storage::disk('public')->delete($oldStoragePath);
            }
        }

        // Save to storage/app/public/concurs_posters/<name>
        $storedPath = $file->storeAs('concurs_posters', $name, 'public');

        // Write full absolute URL
        $cycle->poster_url = asset('storage/' . $storedPath);
        $cycle->save();

        return back()->with('status', 'Poster actualizat.');
    }

    /**
     * Remove poster from a cycle
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'cycle_id' => ['required', 'integer', 'exists:contest_cycles,id'],
        ]);

        $cycle = ContestCycle::findOrFail($data['cycle_id']);

        // Delete file on disk if exists
        if (!empty($cycle->poster_url)) {
            $oldUrlPath = parse_url($cycle->poster_url, PHP_URL_PATH) ?: '';
            $oldStoragePath = ltrim(str_replace('/storage/', '', $oldUrlPath), '/');
            Storage::disk('public')->delete($oldStoragePath);
        }

        $cycle->poster_url = null;
        $cycle->save();

        return back()->with('status', 'Poster eliminat.');
    }
}


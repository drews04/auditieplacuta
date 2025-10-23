<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EventsController extends Controller
{
    public function __construct()
    {
        // Only authenticated users may access the create/store pages.
        $this->middleware('auth')->only(['create', 'store']);
    }

    /**
     * List latest events (newest first)
     */
    public function index(Request $request)
    {
        $events = Event::query()
            ->latest('id')
            ->paginate(6);

        // Mark latest as seen (session + long-lived cookie)
        $latestId = Event::max('id');
        if ($latestId) {
            session(['events_last_seen_id' => $latestId]);
        }

        return response()
            ->view('evenimente.index', compact('events'))
            ->withCookie(cookie(
                'events_last_seen_id',
                (string) $latestId,
                60 * 24 * 180 // 180 days
            ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('evenimente.create');
    }

    /**
     * Store a new event
     */
    public function store(Request $request)
    {
        // Validate
        $data = $request->validate([
            'title'      => ['required', 'string', 'min:6', 'max:160'],
            'event_date' => ['nullable', 'date'],
            'poster'     => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'], // 8 MB
            'body'       => ['nullable', 'string', 'max:50000'],
        ]);

        // Upload poster to public disk
        $path = $request->file('poster')->store('events', 'public');

        // Create event
        $event = Event::create([
            'title'       => $data['title'],
            'event_date'  => $data['event_date'] ?? null,
            'poster_path' => $path,
            'body'        => $data['body'] ?? null,
            'user_id'     => $request->user()->id,
            'slug'        => Str::slug(Str::limit($data['title'], 60)) . '-' . Str::random(6),
        ]);

        // Signal NEW badge globally
        Cache::forever('events_latest_id', $event->id);

        return redirect()
            ->route('events.index')
            ->with('success', 'Eveniment adăugat.');
    }
}

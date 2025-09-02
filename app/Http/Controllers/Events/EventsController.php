<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::latest()->paginate(6);

        // Track the latest seen event for the blinking badge logic
        if ($events->count()) {
            session(['events_last_seen_id' => optional($events->first())->id]);
        }

        return view('evenimente.index', compact('events'));
    }

    public function create()
    {
        // TODO: re-enable when policy is in place
        // $this->authorize('create', Event::class); // optional: add policy later, for now middleware auth is enough
        return view('evenimente.create');
    }

    public function store(Request $request)
    {
        // TODO: re-enable when policy is in place
        // $this->authorize('create', Event::class);

        $data = $request->validate([
            'title'      => ['required', 'string', 'min:6', 'max:160'],
            'event_date' => ['nullable', 'date'],
            'poster'     => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'], // 8MB
            'body'       => ['nullable', 'string', 'max:50000'],
        ]);

        $path = $request->file('poster')->store('events', 'public');

        $event = Event::create([
            'title'       => $data['title'],
            'event_date'  => $data['event_date'] ?? null,
            'poster_path' => $path,
            'body'        => $data['body'] ?? null,
            'user_id'     => $request->user()->id,
            'slug'        => Str::slug(Str::limit($data['title'], 60)) . '-' . Str::random(6),
        ]);

        // Signal NEW badge globally
        cache()->forever('events_latest_id', $event->id);

        return redirect()->route('events.index')->with('success', 'Eveniment adăugat.');
    }
}

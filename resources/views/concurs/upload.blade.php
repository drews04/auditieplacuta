@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/vote-btn.css') }}?v={{ filemtime(public_path('assets/css/vote-btn.css')) }}">
@endpush

@section('title','Încărcă melodie')

@section('content')
<div class="container py-4">

  @includeIf('concurs.partials.phase-switch', [
      'submissionsOpen' => $submissionsOpen ?? false,
      'votingOpen'      => $votingOpen ?? false,
  ])

  @if(session('status')) <div class="alert alert-success my-3">{{ session('status') }}</div> @endif
  @if(session('error'))  <div class="alert alert-danger  my-3">{{ session('error') }}</div>  @endif

  {{-- THEME PILL (today) --}}
  @if(($cycleSubmit ?? null) && $cycleSubmit->theme_text)
    @php
      $p = preg_split('/\s*—\s*/u', $cycleSubmit->theme_text, 2);
      $cat = trim($p[0] ?? ''); $title = trim($p[1] ?? $cycleSubmit->theme_text);
    @endphp
    <div class="ap-theme-row mb-3">
      <div class="ap-left">
        @if($cat!=='')<span class="ap-cat-badge">{{ $cat }}</span><span class="ap-dot">🎯</span>@endif
        <span class="ap-label">Tema de azi:</span>
        <span class="ap-title">{{ $title }}</span>
      </div>
    </div>
  @endif

  {{-- UPLOAD FORM --}}
  <div class="card ap-card mb-4">
    <div class="card-body">
      <h3 class="ap-heading mb-2">Înscrie melodia ta</h3>

      @auth
        @if(!($submissionsOpen ?? false))
          <div class="alert alert-warning mb-3">Înscrierile nu sunt deschise acum.</div>
        @endif

        <form id="upload-form" onsubmit="return false;">
          @csrf
          <label class="form-label">Link YouTube</label>
          <input type="url" name="youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required {{ !($submissionsOpen ?? false) ? 'disabled' : '' }}>
          <button id="upload-btn" class="btn btn-success mt-3" {{ !($submissionsOpen ?? false) ? 'disabled' : '' }}>
            Încarcă
          </button>
          <div id="upload-msg" class="mt-2 small"></div>
        </form>
      @else
        <div class="alert alert-dark">🔒 Autentifică-te pentru a-ți înscrie melodia.</div>
      @endauth
    </div>
  </div>

  {{-- TODAY’S SUBMISSIONS (read-only here) --}}
  <div class="card ap-card">
    <div class="card-body">
      <h5 class="mb-3">Melodii înscrise</h5>
      @include('partials.songs_list', [
        'songs' => $songsSubmit ?? collect(),
        'userHasVotedToday' => true,
        'showVoteButtons' => false
      ])
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('upload-form');
  const btn  = document.getElementById('upload-btn');
  const msg  = document.getElementById('upload-msg');
  if (!form || !btn) return;

  btn.addEventListener('click', async () => {
    msg.textContent = '';
    btn.disabled = true;
    try {
      const data = new FormData(form);
      const resp = await fetch('{{ url('/concurs/upload') }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
        body: data
      });
      const json = await resp.json();
      if (!resp.ok) throw new Error(json.message || 'Eroare');
      msg.className = 'mt-2 small text-success';
      msg.textContent = json.message || 'Încărcat.';
      // location.reload(); // optional
    } catch (e) {
      msg.className = 'mt-2 small text-danger';
      msg.textContent = e.message;
    } finally {
      btn.disabled = false;
    }
  });
});
</script>
@endsection

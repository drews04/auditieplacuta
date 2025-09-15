@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/vote-btn.css') }}?v={{ filemtime(public_path('assets/css/vote-btn.css')) }}">
@endpush

@section('title','Votează')

@section('content')
<div class="container py-4">

  @includeIf('concurs.partials.phase-switch', [
      'submissionsOpen' => $submissionsOpen ?? false,
      'votingOpen'      => $votingOpen ?? false,
  ])

  @if(session('status')) <div class="alert alert-success my-3">{{ session('status') }}</div> @endif
  @if(session('error'))  <div class="alert alert-danger  my-3">{{ session('error') }}</div>  @endif

  {{-- THEME PILL (vote round) --}}
  @if(($cycleVote ?? null) && $cycleVote->theme_text)
    @php
      $p = preg_split('/\s*—\s*/u', $cycleVote->theme_text, 2);
      $cat = trim($p[0] ?? ''); $title = trim($p[1] ?? $cycleVote->theme_text);
    @endphp
    <div class="ap-theme-row mb-3">
      <div class="ap-left">
        @if($cat!=='')<span class="ap-cat-badge">{{ $cat }}</span><span class="ap-dot">🎯</span>@endif
        <span class="ap-label">Tema:</span>
        <span class="ap-title">{{ $title }}</span>
      </div>
    </div>
  @endif

  {{-- SONG LIST (with vote buttons) --}}
  <div class="card ap-card">
    <div class="card-body">
      <h3 class="ap-heading mb-3">Votează melodia preferată</h3>

      @if(!($votingOpen ?? false))
        <div class="alert alert-warning">Nu este faza de vot acum.</div>
      @endif

      @include('partials.songs_list', [
        'songs'               => $songsVote ?? collect(),
        'userHasVotedToday'   => $userHasVotedToday ?? false,
        'showVoteButtons'     => ($votingOpen ?? false) && !($userHasVotedToday ?? false),
        'hideDisabledButtons' => false,
        'disabledVoteText'    => null,
      ])
    </div>
  </div>
</div>

{{-- AJAX vote + vanish effect --}}
<script>
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-vote-song]');
  if (!btn) return;
  e.preventDefault();

  const songId = parseInt(btn.getAttribute('data-vote-song'), 10);
  btn.disabled = true;

  try {
    const resp = await fetch('{{ url('/concurs/vote') }}', {
      method: 'POST',
      headers: {
        'Content-Type':'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ song_id: songId })
    });
    const json = await resp.json();
    if (!resp.ok) throw new Error(json.message || 'Eroare');

    // vanish animation class (matches vote-btn.css)
    btn.classList.add('voted');
    btn.textContent = 'Votat';
  } catch (err) {
    alert(err.message);
    btn.disabled = false;
  }
});
</script>
@endsection

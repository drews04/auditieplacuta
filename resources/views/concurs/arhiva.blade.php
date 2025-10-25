{{-- resources/views/concurs/arhiva.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/song-disqualified.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/archive.css') }}?v={{ time() }}">
@endpush

@section('title', $archive->theme_category . ' — ' . $archive->theme_name . ' | Arhivă')
@section('body_class', 'page-concurs')

@section('content')
<div class="container py-5">

  {{-- Navigation Arrows --}}
  <div class="archive-nav d-flex justify-content-between align-items-center mb-4">
    @if($prevArchive)
      <a href="{{ route('concurs.arhiva.show', ['cycleId' => $prevArchive->cycle_id]) }}" 
         class="btn btn-outline-primary archive-nav-btn">
        ← Anterior
      </a>
    @else
      <div></div>
    @endif

    <a href="{{ route('concurs') }}" class="btn btn-outline-secondary">
      🏠 Concurs Actual
    </a>

    @if($nextArchive)
      <a href="{{ route('concurs.arhiva.show', ['cycleId' => $nextArchive->cycle_id]) }}" 
         class="btn btn-outline-primary archive-nav-btn">
        Următor →
      </a>
    @else
      <div></div>
    @endif
  </div>

  {{-- Theme Title & Date --}}
  <div class="text-center mb-5">
    <h1 class="archive-theme-title fw-bold mb-3">
      {{ $archive->theme_category }} — {{ $archive->theme_name }}
    </h1>
    <p class="archive-date">{{ $formattedDate }}</p>
    @if($archive->theme_likes_count > 0)
      <div class="mt-3">
        <span class="badge" style="background: rgba(255, 0, 100, 0.2); border: 2px solid #ff0064; color: #ff0064; font-size: 16px; padding: 8px 16px;">
          ❤️ {{ $archive->theme_likes_count }}
        </span>
      </div>
    @endif
  </div>

  {{-- Winner Card --}}
  <div class="card border-0 mb-4 archive-winner-card">
    <div class="card-body p-4">
      <div class="d-flex align-items-center gap-4">
        <div class="text-center">
          <div class="fs-1 mb-2">🏆</div>
          @if($archive->winner_photo_url)
            <img src="{{ $archive->winner_photo_url }}" 
                 alt="{{ $archive->winner_name }}" 
                 class="archive-winner-photo">
          @endif
        </div>
        <div class="flex-grow-1">
          <h3 class="mb-2 fw-bold" style="color: #16f1d3;">{{ $archive->winner_name }}</h3>
          <p class="mb-3 fs-5" style="color: rgba(255,255,255,0.8);">{{ $archive->winner_song_title }}</p>
          <div class="d-flex gap-3 flex-wrap">
            <span class="badge" style="background: rgba(22, 241, 211, 0.2); border: 2px solid #16f1d3; color: #16f1d3; font-size: 14px; padding: 8px 16px;">
              🗳️ {{ $archive->winner_votes }} {{ $archive->winner_votes === 1 ? 'vot' : 'voturi' }}
            </span>
            <span class="badge" style="background: rgba(255, 215, 0, 0.2); border: 2px solid #ffd700; color: #ffd700; font-size: 14px; padding: 8px 16px;">
              ⭐ {{ $archive->winner_points }} puncte
            </span>
          </div>
        </div>
        <div class="d-flex flex-column gap-2">
          <a href="{{ $archive->winner_song_url }}" target="_blank" rel="noopener" 
             class="btn" style="background: rgba(22, 241, 211, 0.1); border: 2px solid #16f1d3; color: #16f1d3; font-weight: 600;">
            ▶️ Ascultă pe YouTube
          </a>
          <a href="{{ route('concurs.arhiva.show', ['cycleId' => $archive->cycle_id]) }}" 
             class="btn btn-sm" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
             Vezi clasament complet
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Ranking Table --}}
  <div class="card border-0 mb-4">
    <div class="card-body">
      <h5 class="card-title mb-4" style="color: #16f1d3; font-size: 24px; font-weight: 700;">📊 Clasament Final</h5>
      
      <div class="table-responsive">
        <table class="table table-hover archive-ranking-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Jucător</th>
              <th>Melodie</th>
              <th class="text-center">Voturi</th>
              <th class="text-center">Puncte</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="archiveRankings">
            @foreach($rankings as $index => $ranking)
              <tr class="archive-ranking-row {{ $index >= 10 ? 'archive-hidden' : '' }}" data-rank="{{ $ranking['rank'] }}">
                <td class="fw-bold">{{ $ranking['rank'] }}</td>
                <td>{{ $ranking['user_name'] }}</td>
                <td>{{ $ranking['song_title'] }}</td>
                <td class="text-center">{{ $ranking['votes'] }}</td>
                <td class="text-center fw-bold">{{ $ranking['points'] }}</td>
                <td>
                  <a href="{{ $ranking['youtube_url'] }}" target="_blank" rel="noopener" 
                     class="btn btn-sm btn-outline-primary">
                    ▶️
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if(count($rankings) > 10)
        <div class="text-center mt-3">
          <button type="button" id="showMoreRankings" class="btn btn-outline-secondary">
            Mai mult ▼
          </button>
        </div>
      @endif
    </div>
  </div>

  {{-- Winner's Other Winning Posters (Horizontal Carousel) --}}
  @if($winnerPosters->count() > 0)
    <div class="card border-0 mb-4">
      <div class="card-body">
        <h5 class="card-title mb-4" style="color: #16f1d3; font-size: 20px; font-weight: 700;">🎨 Alte teme câștigate de {{ $archive->winner_name }}</h5>
        
        <div class="archive-poster-carousel-container">
          <div class="archive-poster-carousel" id="posterCarousel">
            @foreach($winnerPosters as $poster)
              <div class="archive-poster-item">
                <a href="{{ route('concurs.arhiva.show', ['cycleId' => $poster->cycle_id]) }}" 
                   class="archive-poster-link">
                  @if($poster->poster_url)
                    <img src="{{ $poster->poster_url }}" 
                         alt="{{ $poster->theme_name }}" 
                         class="archive-poster-img">
                  @else
                    <div class="archive-poster-placeholder">
                      <span>{{ $poster->theme_name }}</span>
                    </div>
                  @endif
                  <div class="archive-poster-overlay">
                    <small>{{ $poster->theme_name }}</small>
                  </div>
                </a>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
// Show more rankings
document.getElementById('showMoreRankings')?.addEventListener('click', function() {
  const hiddenRows = document.querySelectorAll('.archive-ranking-row.archive-hidden');
  hiddenRows.forEach(row => row.classList.remove('archive-hidden'));
  this.style.display = 'none';
});

// Horizontal scroll for poster carousel (drag to scroll)
const carousel = document.getElementById('posterCarousel');
if (carousel) {
  let isDown = false;
  let startX;
  let scrollLeft;

  carousel.addEventListener('mousedown', (e) => {
    isDown = true;
    carousel.classList.add('active');
    startX = e.pageX - carousel.offsetLeft;
    scrollLeft = carousel.scrollLeft;
  });

  carousel.addEventListener('mouseleave', () => {
    isDown = false;
    carousel.classList.remove('active');
  });

  carousel.addEventListener('mouseup', () => {
    isDown = false;
    carousel.classList.remove('active');
  });

  carousel.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - carousel.offsetLeft;
    const walk = (x - startX) * 2;
    carousel.scrollLeft = scrollLeft - walk;
  });
}
</script>
@endpush


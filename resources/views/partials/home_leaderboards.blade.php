@php
  use Illuminate\Support\Facades\DB;

  // Top 3 by YEAR points (resets Jan 1). If you prefer all-time, swap to t.all_time_points.
  $top3 = DB::table('v_user_points_totals as t')
      ->join('users as u', 'u.id', '=', 't.user_id')
      ->select('u.id','u.name')
      ->orderByDesc('t.year_points')     // <-- use 't.all_time_points' for all-time
      ->orderBy('u.name')
      ->limit(3)
      ->get()
      ->values();

  $cards = [
    1 => $top3[0] ?? null,
    2 => $top3[1] ?? null,
    3 => $top3[2] ?? null,
  ];
@endphp

<!-- FULL-WIDTH HERO-BLUE WRAPPER (covers the body's #111111) -->
<div class="w-full" style="background-color:#151625;">
  <!-- changed mt-* to pt-* so no black strip shows above -->
  <section class="container mx-auto px-3 md:px-4 pt-28 md:pt-36 relative">
    <div class="rounded-2xl p-5 md:p-6 overflow-hidden"
         style="background:
                radial-gradient(1000px 500px at 0% -10%, rgba(16,185,129,.15), transparent),
                radial-gradient(900px 480px at 110% 0%, rgba(45,212,191,.12), transparent),
                linear-gradient(180deg,#0b1512 0%, #0e1a16 100%);
                border: 1px solid rgba(16,185,129,.25)">

      <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl md:text-2xl font-extrabold tracking-wide m-0">TOP 3 — SĂPTĂMÂNA CURENTĂ</h2>

        <a href="{{ route('leaderboard.positions') }}"
           class="text-emerald-300 hover:text-emerald-200 underline underline-offset-4 font-semibold">
          Vezi tot
        </a>
      </div>
      @if($top3->isEmpty())
        <div class="py-10 text-center opacity-70">Nu există date pentru această săptămână.</div>
      @else
        <a href="{{ route('leaderboard.positions') }}" class="no-underline text-reset block">

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 md:items-end">

            @php
              // desktop order: 2 - 1 - 3 ; mobile: 1,2,3 stacked
              $orders  = [1 => 'order-1 md:order-2', 2 => 'order-2 md:order-1', 3 => 'order-3 md:order-3'];
              $heights = [1 => 'md:h-60', 2 => 'md:h-44', 3 => 'md:h-44'];
              $grads = [
                1 => ['#ffd700','#ffb700'],      // gold
                2 => ['#e5e7eb','#9ca3af'],      // silver
                3 => ['#cd7f32','#8a5a2b'],      // bronze
              ];
            @endphp

            @foreach([1,2,3] as $rank)
              <div class="{{ $orders[$rank] }}">
                <div class="relative overflow-hidden rounded-2xl p-5 {{ $heights[$rank] }} flex flex-col items-center justify-center text-center
                            transition-all duration-200 hover:-translate-y-0.5"
                     style="background:
                            radial-gradient(500px 180px at 50% -20%, rgba(0,245,160,.22), transparent),
                            linear-gradient(180deg,#0e1c18 0%, #0a1512 100%);
                            border: 1px solid rgba(20,184,166,.35);
                            box-shadow: 0 10px 40px -20px rgba(16,185,129,.45);">

                  @php
                    [$c1,$c2] = $grads[$rank];
                    $gid = "cup-grad-$rank";
                  @endphp
                  <svg viewBox="0 0 64 64" width="68" height="68" class="mb-3 drop-shadow" aria-hidden="true">
                    <defs>
                      <linearGradient id="{{ $gid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%"  stop-color="{{ $c1 }}" />
                        <stop offset="100%" stop-color="{{ $c2 }}" />
                      </linearGradient>
                    </defs>
                    <!-- cup bowl -->
                    <path d="M16 12 H48 V24 C48 34 40 42 32 42 C24 42 16 34 16 24 Z" fill="url(#{{ $gid }})"/>
                    <!-- left handle -->
                    <path d="M16 16 H10 C6 16 6 26 10 26 H16" fill="none" stroke="url(#{{ $gid }})" stroke-width="4" stroke-linecap="round"/>
                    <!-- right handle -->
                    <path d="M48 16 H54 C58 16 58 26 54 26 H48" fill="none" stroke="url(#{{ $gid }})" stroke-width="4" stroke-linecap="round"/>
                    <!-- stem + base -->
                    <rect x="28" y="42" width="8" height="8" rx="2" fill="url(#{{ $gid }})"/>
                    <rect x="22" y="50" width="20" height="6" rx="3" fill="url(#{{ $gid }})"/>
                  </svg>

                  <div class="text-base md:text-lg font-semibold">
                    {{ $cards[$rank]->name ?? '—' }}
                  </div>
                </div>
              </div>
            @endforeach

          </div>
        </a>
      @endif
    </div>
  </section>
</div>

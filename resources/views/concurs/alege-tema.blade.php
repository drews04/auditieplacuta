@extends('layouts.app')

@section('title', 'Alege tema • Concurs')
@section('body_class', 'page-concurs')

@section('content')
  <div class="alege-tema-page container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="neon-card alege-tema-card p-4 p-md-5 text-center">
          <h1 class="neon-title mb-2">Felicitări, {{ $winnerName }}!</h1>
          <p class="neon-sub mb-4">Alege tema pentru concursul de mâine</p>

          @if(session('success'))
            <div class="alert alert-success fw-bold mb-4">{{ session('success') }}</div>
          @endif

          @if ($errors->any())
            <div class="alert alert-danger text-start">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Reuse the same picker used by the admin Start modal --}}
          <div class="mt-3 d-flex flex-column align-items-center">
            @include('concurs.partials.theme_picker', [
              'action'      => route('concurs.alege-tema.store'),
              'submitLabel' => 'Salvează tema'
            ])

            <a href="{{ route('concurs') }}" class="btn-ghost mt-3">Înapoi la Concurs</a>
          </div>

          <div class="mt-4 small text-light-emphasis alege-tema-note">
            * Ai timp până la ora <strong>21:00</strong> să confirmi tema.
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('.ap-theme-picker form');
  if (form) {
    console.log('✅ Theme picker form found:', form.action);
    form.addEventListener('submit', function(e) {
      console.log('🚀 Form is submitting...', {
        category: form.querySelector('[name="category"]').value,
        theme: form.querySelector('[name="theme"]').value,
        action: form.action,
        method: form.method
      });
    });
  } else {
    console.error('❌ Theme picker form NOT found');
  }
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ filemtime(public_path('assets/css/concurs-winner.css')) }}">
<style>
/* reuse your neon tokens */
.neon-card{
  background:#000; color:#eafdf9; border:3px solid #16f1d3; border-radius:16px;
  box-shadow:0 0 18px #16f1d3, 0 0 44px rgba(22,241,211,.55);
}
.neon-title{
  font-family:'Orbitron',system-ui; font-weight:900; color:#16f1d3;
  text-shadow:0 0 10px #16f1d3, 0 0 22px #16f1d3; font-size:clamp(28px,4.8vw,42px);
}
.neon-sub{
  color:#16f1d3; font-weight:800; text-shadow:0 0 8px #16f1d3; font-size:clamp(16px,3vw,22px);
}
.btn-ghost{
  background:transparent; color:#16f1d3; font-weight:800; border:2px solid #16f1d3; border-radius:12px; padding:.9rem 1.4rem;
  box-shadow:0 0 10px rgba(22,241,211,.35) inset, 0 0 10px rgba(22,241,211,.35);
}
</style>
@endpush

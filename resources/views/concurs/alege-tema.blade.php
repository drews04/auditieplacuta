@extends('layouts.app')


@section('title', 'Alege tema • Concurs')
@section('body_class', 'page-concurs')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/alege-tema.css') }}">
@endpush
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

        <form method="POST" action="{{ route('concurs.alege-tema.store') }}" class="mt-3">
          @csrf
          <div class="d-flex flex-column align-items-center gap-3">

            {{-- Category & Theme inputs --}}
            <div class="d-flex flex-wrap justify-content-center gap-2 alege-tema-inputs">
              <select name="category" id="category" class="form-select alege-tema-select" style="max-width:220px;">
                @foreach($categories as $c)
                  <option value="{{ $c['code'] }}" @disabled($c['disabled'])>
                    {{ $c['label'] }} @if($c['disabled']) (indisponibil) @endif
                  </option>
                @endforeach
              </select>

              <input type="text" name="theme" id="theme"
                     class="form-control alege-tema-text"
                     placeholder="ex: Thunder"
                     style="max-width:320px;"
                     value="{{ old('theme') }}">
            </div>

            {{-- Live preview --}}
            <div class="w-pill mt-2 alege-tema-preview" aria-live="polite">
              Tema finală: <strong id="preview">—</strong>
            </div>

            {{-- Buttons --}}
            <div class="d-flex gap-3 mt-3 alege-tema-buttons">
              <button type="submit" class="btn-neon">Salvează tema</button>
              <a href="{{ url('/concurs') }}" class="btn-ghost">Înapoi la Concurs</a>
            </div>
          </div>
        </form>

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
(function(){
  const cat = document.getElementById('category');
  const th  = document.getElementById('theme');
  const pv  = document.getElementById('preview');
  function update(){ 
    const c = (cat?.value || '').trim();
    const t = (th?.value || '').trim();
    pv.textContent = t ? `${c}: ${t}` : (c ? `${c}: ` : '—');
  }
  cat?.addEventListener('change', update);
  th?.addEventListener('input', update);
  update();
})();
</script>
@endpush

@push('styles')
<style>
/* reuse your neon tokens from winner.css */
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
.w-pill{
  display:inline-flex; align-items:center; gap:8px; border:2px solid #16f1d3; border-radius:999px;
  padding:8px 14px; color:#16f1d3; box-shadow:0 0 10px rgba(22,241,211,.45) inset, 0 0 10px rgba(22,241,211,.25);
}
.btn-neon{
  background:linear-gradient(135deg,#16f1d3,#7afbe8); color:#001311; font-weight:900; letter-spacing:.3px;
  border:0; border-radius:12px; padding:.9rem 1.4rem; box-shadow:0 0 16px #16f1d3, 0 0 36px rgba(22,241,211,.55);
}
.btn-neon:hover{ transform:translateY(-1px) scale(1.03) }
.btn-ghost{
  background:transparent; color:#16f1d3; font-weight:800; border:2px solid #16f1d3; border-radius:12px; padding:.9rem 1.4rem;
  box-shadow:0 0 10px rgba(22,241,211,.35) inset, 0 0 10px rgba(22,241,211,.35);
}
</style>
@endpush
@extends('layouts.app')

@section('title','Concurs')

@push('styles')
<style>
  .hub-wrap{display:flex;justify-content:center;margin:64px 0}
  .hub{display:grid;grid-template-columns:1fr 1fr;gap:24px}
  @media (max-width: 768px){ .hub{grid-template-columns:1fr;gap:16px} }
  .hub-btn{
    display:flex;align-items:center;justify-content:center;
    padding:18px 26px;border-radius:14px;text-decoration:none;font-weight:800;
    border:2px solid #27f0d0; box-shadow:0 0 22px rgba(39,240,208,.35) inset;
    transition:transform .08s ease, opacity .2s ease;
  }
  .hub-btn--vote{ background:#0e1621; color:#fff; }
  .hub-btn--upload{ background:#0e1f19; color:#fff; }
  .hub-btn.is-disabled{opacity:.4;pointer-events:none;filter:grayscale(25%)}
  .hub-btn:active{ transform:translateY(1px) }
</style>
@endpush

@section('content')
<div class="container">
  <div class="hub-wrap">
    <div class="hub">
      {{-- LEFT: VOTEAZĂ --}}
      <a href="{{ route('concurs.vote.page') }}"
         class="hub-btn hub-btn--vote {{ !($votingOpen ?? false) ? 'is-disabled' : '' }}"
         aria-disabled="{{ !($votingOpen ?? false) ? 'true' : 'false' }}">
        ★ Votează
      </a>

      {{-- RIGHT: ÎNCARCĂ --}}
      <a href="{{ route('concurs.upload.page') }}"
         class="hub-btn hub-btn--upload {{ !($submissionsOpen ?? false) ? 'is-disabled' : '' }}"
         aria-disabled="{{ !($submissionsOpen ?? false) ? 'true' : 'false' }}">
        ⬆️ Încarcă melodia
      </a>
    </div>
  </div>
</div>
@endsection

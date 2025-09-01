@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/concurs.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/regulament.css') }}?v={{ time() }}">
@endpush

@section('title','Regulament Concurs')
@section('body_class','page-concurs page-regulament')

@section('content')
<div class="container py-5 ap-regulament" style="margin-top:84px">
  <h1 class="mb-3" style="font-weight:800; letter-spacing:1px;">📜 Regulament — Concursul AuditiePlacuta</h1>
  

  {{-- Scop --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>1. Scopul concursului</h2>
      <ul>
        <li>În fiecare zi lucrătoare se alege o <strong>temă</strong> muzicală; utilizatorii înscriu câte un clip YouTube care se potrivește temei.</li>
        <li>După etapa de înscriere urmează <strong>votul</strong>; melodia cu cele mai multe voturi câștigă.</li>
      </ul>
    </div>
  </div>

  {{-- Program --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>2. Program &amp; calendar</h2>
      <ul>
        <li><span class="badge-soft">Luni–Vineri</span> — concurs activ. <em>În weekend nu se ține concurs</em>.</li>
        <li><strong>Înscrieri</strong>: deschise dimineața până la <strong>19:30</strong> (ora RO).</li>
        <li><strong>Vot</strong>: <strong>20:00 → 20:00</strong> (24h) pentru runda precedentă.</li>
        <li><strong>Alegerea temei</strong>: câștigătorul are timp până la <strong>21:00</strong> în ziua încheierii votului.</li>
        <li>În lipsa unei alegeri până la termen, sistemul pornește automat un <strong>fallback</strong> din <em>ThemePool</em>.</li>
      </ul>
    </div>
  </div>

  {{-- Înscriere --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>3. Înscrierea melodiilor</h2>
      <ul>
        <li><strong>1 melodie / utilizator / zi</strong>. Link valid YouTube, titlu corect, fără duplicate grosolane.</li>
        <li>Melodiile sunt afișate <strong>anonim</strong> (fără nume utilizator) până la închiderea votului.</li>
        <li>Dacă ai fost descalificat (vezi pct. 7), poți <strong>reîncărca</strong> o altă melodie în aceeași rundă, cât timp fereastra de înscrieri e deschisă.</li>
      </ul>
    </div>
  </div>

  {{-- Vot --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>4. Votul</h2>
      <ul>
        <li><strong>1 vot / utilizator / rundă</strong>. Sistemul blochează voturi multiple.</li>
        <li><strong>Nu îți poți vota propria melodie</strong>; încercarea este respinsă cu mesaj.</li>
        <li>După închiderea rundei, proprietarul fiecărei melodii poate vedea <strong>cine a votat</strong> la melodia lui (listă privată). Admin poate vedea toate listele.</li>
      </ul>
    </div>
  </div>

  {{-- Alegerea temei --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>5. Alegerea temei</h2>
      <ul>
        <li>Câștigătorul vizualizează un mesaj cu confetti &amp; buton “Alege tema”.</li>
        <li>Tema trebuie aleasă până la <strong>21:00</strong>. Sistemul reamintește periodic (snooze popup).</li>
        <li>Nealeasă la timp → <strong>fallback automat</strong> din ThemePool pentru a menține fluxul zilnic.</li>
      </ul>
    </div>
  </div>

  {{-- Egalități --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>6. Egalități &amp; revot</h2>
      <ul>
        <li>La egalitate, se pornește un <strong>mini-revot</strong> de <strong>30 minute</strong> doar între melodiile la egalitate.</li>
        <li>După revot, câștigătorul are <strong>30 minute</strong> rămase pentru a alege tema.</li>
      </ul>
    </div>
  </div>

  {{-- Descalificare --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>7. Descalificare (Admin)</h2>
      <ul>
        <li>Admin poate <strong>descalifica</strong> o melodie pentru:
          limbaj sau conținut nepotrivit, link invalid/indisponibil, încălcări ale regulilor sau tentative de manipulare.</li>
        <li>Efecte:
          <ul>
            <li>Melodia este exclusă din tally-ul de voturi pentru acea rundă.</li>
            <li>Se <strong>revocă</strong> eventualele puncte atribuite acelei înscrieri.</li>
            <li>Utilizatorul poate <strong>reîncărca</strong> o altă melodie în aceeași rundă (dacă etapa de înscrieri e încă deschisă).</li>
            <li>Punctele de participare se <strong>recalculează</strong> după noua încărcare.</li>
          </ul>
        </li>
      </ul>
    </div>
  </div>

  {{-- Like-uri pe teme --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>8. Aprecieri (♥) pentru teme</h2>
      <ul>
        <li>Utilizatorii pot acorda <strong>like</strong> temelor alese într-o rundă.</li>
        <li>Se afișează numărul total de like-uri și lista utilizatorilor care au apreciat tema.</li>
        <li>În fiecare lună, pe data de <strong>1</strong>, se afișează <strong>Top 3 teme</strong> cu cele mai multe aprecieri în luna anterioară.</li>
      </ul>
    </div>
  </div>

  {{-- Fair-play --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>9. Fair-play &amp; comportament</h2>
      <ul>
        <li>Respect între participanți. Fără spam, insulte, voturi coordonate sau alte tentative de fraudă.</li>
        <li>Admin poate restricționa temporar sau permanent accesul în caz de abuz.</li>
      </ul>
    </div>
  </div>

  {{-- Modificări --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2>10. Modificări ale regulamentului</h2>
      <ul>
        <li>Regulile pot fi ajustate pentru a îmbunătăți experiența. Orice modificare va fi anunțată în pagină.</li>
      </ul>
    </div>
  </div>

  <div class="mt-4">
    <a href="{{ route('concurs') }}" class="btn btn-primary btn-sm">⟵ Înapoi la Concurs</a>
  </div>
</div>
@endsection

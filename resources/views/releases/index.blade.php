@extends('layouts.app')
@section('title', 'Noutăți în muzică')
@section('body_class', 'page-releases')

@php use Illuminate\Support\Str; @endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/releases.css') }}">
@endpush

@section('content')
<div class="container py-5">

  {{-- Title + Admin add button --}}
  @php $isAdmin = auth()->check() && (auth()->user()->is_admin ?? false); @endphp
  <div class="title-bar">
    <h1 class="fw-bold text-neon">Noutăți în muzică</h1>
    @if($isAdmin)
      <a href="{{ route('admin.releases.create') }}" class="btn btn-neon btn-sm">+ Adaugă lansare</a>
    @endif
  </div>

  {{-- HERO release (newest / highlight) --}}
  @if($hero)
    <article class="neon-card hero-release d-flex flex-wrap mb-5">
      <div class="hero-poster flex-shrink-0">
        <img src="{{ $hero->cover_path ? asset('storage/'.$hero->cover_path) : asset('assets/img/placeholder-cover.jpg') }}"
             alt="{{ $hero->title }}">
      </div>
      <div class="hero-body p-3 p-md-4 flex-grow-1">
       <div class="artist-ribbon"><span>{{ $hero->artists->pluck('name')->join(', ') }}</span></div>

        <h3 class="mb-2">{{ $hero->title }}</h3>
        <div class="mb-2 text-accent small">
          {{ $hero->categories->pluck('name')->join(' | ') }}
        </div>
        <p>{{ Str::limit($hero->description, 250) }}</p>
        <a href="{{ route('releases.show',$hero->slug) }}" class="btn btn-neon mt-2">Citește mai mult</a>
      </div>
    </article>
  @endif

  {{-- Other releases this week — EXACT same layout/styles as hero --}}
  @if($releases->count())
    <h4 class="text-neon mb-3">Alte lansări din săptămâna {{ $weekKey }}</h4>

    @foreach($releases as $r)
      <article class="neon-card hero-release d-flex flex-wrap mb-5">
        <div class="hero-poster flex-shrink-0">
          <img src="{{ $r->cover_path ? asset('storage/'.$r->cover_path) : asset('assets/img/placeholder-cover.jpg') }}"
               alt="{{ $r->title }}">
        </div>
        <div class="hero-body p-3 p-md-4 flex-grow-1">
        <div class="artist-ribbon"><span>{{ $hero->artists->pluck('name')->join(', ') }}</span></div>
          <h3 class="mb-2">{{ $r->title }}</h3>
          <div class="mb-2 text-accent small">
            {{ $r->categories->pluck('name')->join(' | ') }}
          </div>
          <p>{{ Str::limit($r->description, 250) }}</p>
          <a href="{{ route('releases.show',$r->slug) }}" class="btn btn-neon mt-2">Citește mai mult</a>
        </div>
      </article>
    @endforeach
  @else
    <div class="neon-card p-4 text-center opacity-75">Nicio lansare găsită pentru săptămâna {{ $weekKey }}</div>
  @endif

</div>
@endsection

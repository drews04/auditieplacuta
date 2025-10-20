@extends('layouts.app')

@section('title', 'Acasă – Auditie Placuta')
@section('content')
@section('body_class', 'page-home')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}?v={{ filemtime(public_path('assets/css/home.css')) }}">
@endpush

<div id="sc-banner" class="sc-banner banner-bg position-relative">
  <div class="container">
      <div class="banner-content text-center">
          <img class="banner-icon wow fadeInUp"
               data-wow-delay="0.4s"
               data-wow-duration="0.7s"
               src="{{ asset('assets/images/icons/icon1.png') }}"
               alt="icon-image"/>

          <h1 class="banner-title wow fadeInUp"
              data-wow-delay="0.4s"
              data-wow-duration="0.7s">
              Descoperă Muzica Împreună Cu Noi
          </h1>

          <div class="description wow fadeInUp"
               data-wow-delay="0.4s"
               data-wow-duration="0.7s">
              Te așteptăm să îți arăți talentul
          </div>

          {{-- CTA --}}
          @auth
            <a href="{{ route('concurs') }}"
               class="banner-btn wow fadeInUp black-shape-big"
               data-wow-delay="0.4s"
               data-wow-duration="0.7s">
                <span class="btn-text">Intră în Concurs</span>
                <span class="hover-shape1"></span>
                <span class="hover-shape2"></span>
                <span class="hover-shape3"></span>
            </a>
          @else
            <a href="{{ route('register') }}"
               class="banner-btn wow fadeInUp black-shape-big"
               data-wow-delay="0.4s"
               data-wow-duration="0.7s">
                <span class="btn-text">Înscrie-te acum</span>
                <span class="hover-shape1"></span>
                <span class="hover-shape2"></span>
                <span class="hover-shape3"></span>
            </a>
          @endauth
      </div>
  </div>
</div>

<!-- Banner Section End -->
@include('partials.home_leaderboards')

@endsection

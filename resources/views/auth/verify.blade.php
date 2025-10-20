{{-- resources/views/auth/verify.blade.php --}}
@extends('layouts.guest')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/register.css') }}">
@endpush

@section('content')
<div class="guest-container">
  <div class="register-container">

    <div class="logo">
      <img src="{{ asset('assets/images/logo.png') }}" alt="Auditie Placuta">
    </div>

    <h2>Verificare Email</h2>

    <p class="text-center" style="margin-top:-.3rem; margin-bottom:1rem;">
      Ți-am trimis un cod de verificare pe email. Introdu-l mai jos pentru a finaliza înregistrarea.
    </p>

    @if ($errors->has('verification_code'))
      <div class="alert alert-danger">
        {{ $errors->first('verification_code') }}
      </div>
    @endif

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('verify.code') }}">
      @csrf

      <div class="form-group">
        <label for="verification_code">Cod de verificare</label>
        <input
          type="text"
          id="verification_code"
          name="verification_code"
          required
          placeholder="Introdu codul primit pe email">
      </div>

      <button type="submit" class="register-btn">
        Confirmă Codul
      </button>
    </form>

    <div class="resend-link">
      <a href="{{ route('password.request') }}">
        Trimite din nou codul
      </a>
    </div>

  </div>
</div>
@endsection

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

    <h2>Resetează parola</h2>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
      @csrf

      <div class="form-group">
        <label for="code">Cod primit prin email</label>
        <input
          type="text"
          id="code"
          name="code"
          required
          placeholder="Introdu codul primit pe email">
      </div>

      <div class="form-group">
        <label for="new_password">Parolă nouă</label>
        <input
          type="password"
          id="new_password"
          name="new_password"
          required
          placeholder="Alege o parolă sigură">
      </div>

      <div class="form-group">
        <label for="new_password_confirmation">Confirmă parola</label>
        <input
          type="password"
          id="new_password_confirmation"
          name="new_password_confirmation"
          required
          placeholder="Rescrie parola">
      </div>

      <button type="submit" class="register-btn">
        Resetează parola
      </button>
    </form>

    <div class="resend-link">
      <a href="{{ route('password.request') }}">Trimite alt cod</a>
    </div>

  </div>
</div>
@endsection

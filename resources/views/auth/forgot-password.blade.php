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

    <h2>Resetare parolă</h2>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
      @csrf

      <div class="form-group">
        <label for="email">Adresă de email</label>
        <input
          type="email"
          id="email"
          name="email"
          required
          placeholder="exemplu@email.com"
          value="{{ old('email') }}"
        >
      </div>

      <button type="submit" class="register-btn">
        Trimite codul de resetare
      </button>
    </form>
  </div>
</div>
@endsection

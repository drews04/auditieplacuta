@extends('layouts.guest')

@section('content')
<body class="guest-body">
  <div class="guest-container">
    <div class="register-container">
      <div class="logo">
        <img src="{{ asset('assets/images/logo.png') }}"> {{-- Replace with your real path --}}
      </div>

      <h2>Resetare parolă</h2>

      @if ($errors->any())
        <div class="alert-danger">
          <ul style="margin-bottom: 0;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
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
          >
        </div>

        <button type="submit" class="register-btn">
          Trimite codul de resetare
        </button>
      </form>
    </div>
  </div>
</body>
@endsection

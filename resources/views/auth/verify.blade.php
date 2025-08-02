@extends('layouts.guest')

@section('content')
<div class="verify-container">
    <div class="success-icon" style="font-size: 48px; text-align: center; margin-bottom: 10px;">
        ✅
    </div>

    <h2 style="text-align:center; margin-bottom: 10px;">Verificare Email</h2>

    <p style="text-align:center; margin-bottom: 20px;">
        Ți-am trimis un cod de verificare pe adresa ta de email. <br>
        Te rugăm să îl introduci mai jos pentru a finaliza înregistrarea.
    </p>

    {{-- ✅ Show error if code is wrong --}}
    @if ($errors->has('verification_code'))
        <div class="alert alert-danger">
            {{ $errors->first('verification_code') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verify.code') }}">
        @csrf

        <div class="form-group">
            <label for="verification_code">Cod de verificare</label>
            <input
                type="text"
                id="verification_code"
                name="verification_code"
                class="form-control @error('verification_code') is-invalid @enderror"
                required
                placeholder="Introdu codul primit pe email"
            >
        </div>

        <button type="submit" class="register-btn">Confirmă Codul</button>
    </form>
</div>
@endsection

@extends('layouts.guest')

@section('content')
<div class="guest-container">
    <div class="register-container">

        <div class="logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
        </div>

        <h2>RESETEAZĂ PAROLA</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin-bottom: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <div class="form-group">
                <label for="code">Cod primit prin email</label>
                <input type="text" id="code" name="code" required>
            </div>

            <div class="form-group">
                <label for="new_password">Alege parola nouă</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">Confirmă parola nouă</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
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
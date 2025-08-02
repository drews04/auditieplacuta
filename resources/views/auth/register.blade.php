@extends('layouts.guest')

@section('content')
<div class="guest-container">
    <div class="register-container">
        <div class="logo" style="text-align:center; margin-bottom: 30px;">
            <img src="/logo.png" alt="Auditie Placuta" style="max-width: 200px;">
        </div>

        <h2>Înregistrare</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('verification_message'))
            <div class="alert alert-success">
                {{ session('verification_message') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">Nume</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="Acesta va fi numele tău afișat pe site"
                >
            </div>

            <div class="form-group">
                <label for="email">Adresă de email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    placeholder="exemplu@email.com"
                >
            </div>

            <div class="form-group">
                <label for="password">Parolă</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Alege o parolă sigură"
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmă parola</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    placeholder="Rescrie parola"
                >
            </div>

            <button type="submit" class="register-btn">Înregistrează-te</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Se trimite...'; // you can also add a spinner icon
        });
    });
</script>
@endsection

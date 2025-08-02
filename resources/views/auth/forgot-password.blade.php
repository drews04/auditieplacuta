<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resetare parolă</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}"> {{-- Optional: your own styling --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div class="auth-form-container">
    <h2>Resetare parolă</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
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
            <label for="email">Adresa de email</label>
            <input
                type="email"
                id="email"
                name="email"
                required
            >
        </div>

        <button type="submit" class="btn">
            Trimite codul de resetare
        </button>
    </form>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #121212; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div class="card p-4" style="width: 100%; max-width: 400px; background-color: #1e1e1e; border: 1px solid #333;">
        <h4 class="mb-3 text-center text-white">Reset Your Password</h4>

        {{-- Show errors if any --}}
        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li style="font-size: 14px;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Reset form --}}
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group mb-3">
                <label for="code" class="form-label text-white">Code received by email</label>
                <input type="text" name="code" id="code" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="new_password" class="form-label text-white">Alege noua parola</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="new_password_confirmation" class="form-label text-white">Confirma noua parola</label>
                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Reseteaza Parola</button>
        </form>

        {{-- Resend code --}}
        <form method="POST" action="{{ route('password.email') }}" class="mt-3">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <button type="submit" class="btn btn-link text-decoration-underline text-white w-100">
                Trimite alt cod
            </button>
        </form>
    </div>

</body>
</html>


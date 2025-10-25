

@section('content')
    <div class="login-container">
        <h2>Autentificare</h2>

        {{-- ✅ Show success message if redirected after registration --}}
        @if (session('success'))
            <div class="alert alert-success">
                <span class="checkmark-icon">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

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
                    placeholder="Parola ta"
                >
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember">
                    Ține-mă minte
                </label>
            </div>

            <button type="submit">Autentifică-te</button>
        </form>
    </div>
@endsection
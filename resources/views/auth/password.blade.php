@extends('layouts.app') {{-- Or the layout you used for register --}}

@section('content')
<div class="auth-form-container">
    <h2>Schimbă parola</h2>

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

    <form method="POST" action="{{ route('password.change') }}">
        @csrf

        <div class="form-group">
            <label for="current_password">Parola curentă</label>
            <input
                type="password"
                id="current_password"
                name="current_password"
                required
            >
        </div>

        <div class="form-group">
            <label for="new_password">Parola nouă</label>
            <input
                type="password"
                id="new_password"
                name="new_password"
                required
            >
        </div>

        <div class="form-group">
            <label for="new_password_confirmation">Confirmă parola nouă</label>
            <input
                type="password"
                id="new_password_confirmation"
                name="new_password_confirmation"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Actualizează parola
        </button>
    </form>
</div>
@endsection

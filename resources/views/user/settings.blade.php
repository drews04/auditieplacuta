@extends('layouts.app')

@section('content')
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="user-page-wrapper container py-5">
    <h1>⚙️ Setări cont</h1>

    <div class="card user-card mt-4 shadow-sm">
        <div class="card-body">
            {{-- Change Email --}}
            <div class="mb-4">
                <h5 class="text-info">📧 Schimbă emailul</h5>
                <form method="POST" action="{{ route('user.settings.updateEmail') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email nou</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-info">Actualizează</button>
                </form>
            </div>

            <hr>

            {{-- Change Password --}}
            <div class="mb-4">
                <h5 class="text-info">🔒 Schimbă parola</h5>
                <form method="POST" action="{{ route('user.settings.updatePassword') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Parolă curentă</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parolă nouă</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmă noua parolă</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-info">Schimbă parola</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
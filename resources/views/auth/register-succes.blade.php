@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="alert alert-success d-flex align-items-center">
            <span class="me-2" style="font-size: 18px;">✅</span>
            <div>
                Cont creat cu succes! Te poți autentifica.
            </div>
        </div>

        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const showModal = new bootstrap.Modal(document.getElementById('loginModal'));
                showModal.show();
            });
        </script>
    </div>
@endsection
@extends('layouts.app')

@section('title', 'Creează Thread Nou - Forum')

@section('body_class', 'page-forum')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/forum.css') }}">
@endpush

@section('content')
<div class="forum-container">
    <div class="container">
        <!-- Forum Header -->
        <div class="forum-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Creează Thread Nou</h1>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('forum.home') }}" class="btn btn-new-thread">
                        <i class="fas fa-arrow-left me-2"></i>Înapoi la Forum
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Error Messages -->
        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Thread Creation Form -->
        <div class="forum-actions">
            <form action="{{ route('forum.threads.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="category_id" class="form-label text-light">Categorie</label>
                    <select name="category_id" id="category_id" class="form-control forum-select" required>
                        <option value="">Selectează o categorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label text-light">Titlu</label>
                    <input type="text" name="title" id="title" class="form-control forum-input" 
                           value="{{ old('title') }}" required minlength="4" maxlength="140">
                    @error('title')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="body" class="form-label text-light">Conținut</label>
                    <textarea name="body" id="body" class="form-control forum-textarea" 
                              rows="8" required minlength="10">{{ old('body') }}</textarea>
                    @error('body')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('forum.home') }}" class="btn btn-secondary">Anulează</a>
                    <button type="submit" class="btn btn-new-thread">
                        <i class="fas fa-plus me-2"></i>Creează Thread
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/forum.js') }}"></script>
@endpush

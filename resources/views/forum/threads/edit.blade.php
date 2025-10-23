{{-- resources/views/forum/threads/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editează thread - Forum')
@section('body_class', 'page-forum')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/forum.css') }}">
@endpush

@section('content')
<div class="forum-container">
  <div class="container">

    {{-- Header --}}
    <div class="forum-header">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1>Editează thread</h1>
          <div class="forum-thread-meta mt-2">
            <span class="forum-thread-author">
              <i class="fas fa-user me-2"></i>{{ $thread->user->name ?? 'Utilizator' }}
            </span>
            <span class="forum-thread-time">
              <i class="fas fa-clock me-2"></i>{{ $thread->created_at->diffForHumans() }}
            </span>
          </div>
        </div>
        <div class="col-md-4 text-md-end">
          <a href="{{ route('forum.threads.show', $thread->slug) }}" class="btn btn-secondary">← Înapoi la thread</a>
        </div>
      </div>
    </div>

    {{-- Errors --}}
    @if ($errors->any())
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    {{-- Form (update only) --}}
    <div class="forum-actions">
      <form method="POST" action="{{ route('forum.threads.update', $thread->slug) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label for="title" class="form-label forum-label">Titlu</label>
          <input id="title" name="title" type="text"
                 class="form-control forum-input"
                 required minlength="3" maxlength="140"
                 value="{{ old('title', $thread->title) }}">
        </div>

        <div class="mb-3">
          <label for="category_id" class="form-label forum-label">Categorie</label>
          <select id="category_id" name="category_id" class="form-control forum-select">
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}"
                {{ (int) old('category_id', $thread->category_id) === (int) $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label for="body" class="form-label forum-label">Conținut</label>
          <textarea id="body" name="body" rows="10"
                    class="form-control forum-textarea"
                    required minlength="5">{{ old('body', $thread->body) }}</textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="{{ route('forum.threads.show', $thread->slug) }}" class="btn btn-secondary">Anulează</a>
          <button type="submit" class="btn btn-new-thread">
            <i class="fas fa-save me-2"></i>Salvează
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection

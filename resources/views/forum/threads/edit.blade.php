@extends('layouts.app')

@section('title', 'Editează Thread - Forum')
@section('body_class', 'page-forum')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/forum.css') }}">
@endpush

@section('content')
<div class="forum-container">
  <div class="container">

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

    @if ($errors->any())
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if (session('success'))
      <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="forum-actions">
      <form method="POST" action="{{ route('forum.threads.update', $thread) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label for="category_id" class="form-label forum-label">Categorie</label>
          <select name="category_id" id="category_id" class="form-control forum-select">
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}"
                {{ (old('category_id', $thread->category_id) == $cat->id) ? 'selected' : '' }}>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label for="title" class="form-label forum-label">Titlu</label>
          <input type="text" id="title" name="title" class="form-control forum-input"
                 value="{{ old('title', $thread->title) }}" required minlength="4" maxlength="140">
        </div>

        <div class="mb-3">
          <label for="body" class="form-label forum-label">Conținut</label>
          <textarea id="body" name="body" rows="10" class="form-control forum-textarea" required>{{ old('body', $thread->body) }}</textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="{{ route('forum.threads.show', $thread->slug) }}" class="btn btn-secondary">Anulează</a>

          <div class="d-flex gap-2">
            @can('delete', $thread)
            <form method="POST" action="{{ route('forum.threads.destroy', $thread) }}"
                  onsubmit="return confirm('Sigur ștergi acest thread? Această acțiune nu poate fi anulată.');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-secondary">Șterge</button>
            </form>
            @endcan

            @can('update', $thread)
            <button type="submit" class="btn btn-new-thread">Salvează</button>
            @endcan
          </div>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/forum.js') }}"></script>
@endpush

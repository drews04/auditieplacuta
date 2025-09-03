@extends('layouts.app')

@section('title', 'Editează răspunsul - Forum')
@section('body_class', 'page-forum')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/forum.css') }}">
@endpush

@section('content')
<div class="forum-container">
  <div class="container">

    <!-- Header -->
    <div class="forum-header">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1>Editează răspunsul</h1>
          <div class="forum-thread-meta mt-2">
            <span class="forum-thread-author">
              <i class="fas fa-user me-2"></i>{{ $post->user->name ?? 'Utilizator' }}
            </span>
            <span class="forum-thread-time">
              în thread: <strong>{{ $post->thread->title }}</strong>
            </span>
          </div>
        </div>
        <div class="col-md-4 text-md-end">
          <a href="{{ route('forum.threads.show', $post->thread->slug) }}#post-{{ $post->id }}" class="btn btn-new-thread">
            <i class="fas fa-arrow-left me-2"></i>Înapoi la thread
          </a>
        </div>
      </div>
    </div>

    <!-- Errors -->
    @if ($errors->any())
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- Edit form -->
    <div class="forum-actions">
      <form method="POST" action="{{ route('forum.posts.update', $post) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label class="form-label forum-label">Conținut</label>
          <textarea
            name="body"
            class="form-control forum-textarea"
            rows="6"
            required
            minlength="2"
          >{{ old('body', $post->body) }}</textarea>
        </div>

        <div class="d-flex justify-content-between">
          <a href="{{ route('forum.threads.show', $post->thread->slug) }}#post-{{ $post->id }}" class="btn btn-secondary">
            Anulează
          </a>
          <button type="submit" class="btn btn-new-thread">
            <i class="fas fa-save me-2"></i>Salvează
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection

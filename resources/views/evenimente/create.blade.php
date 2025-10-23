@extends('layouts.app')
@section('title', 'Adaugă eveniment')
@section('body_class', 'page-events-create')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/events.css') }}">
@endpush

@section('content')
<div class="events-page-wrap">
  <div class="container py-5">
    <div class="neon-card p-4">
      <h1 class="h4 fw-bold mb-4">Adaugă eveniment</h1>
      <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="event-form">
        @csrf
        <div class="mb-3">
          <label class="form-label">Titlu</label>
          <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="160">
          @error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Data evenimentului (opțional)</label>
          <input type="date" name="event_date" class="form-control" value="{{ old('event_date') }}">
          @error('event_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Poster (JPG/PNG/WebP, max 8MB)</label>
          <input type="file" name="poster" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
          @error('poster')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
          <label class="form-label">Descriere (opțional)</label>
          <textarea name="body" rows="6" class="form-control" maxlength="50000" placeholder="Detalii: locație, oră, preț bilet etc.">{{ old('body') }}</textarea>
          @error('body')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <button class="btn btn-neon">Salvează</button>
      </form>
    </div>
  </div>
</div>
@endsection

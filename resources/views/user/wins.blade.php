@extends('layouts.app')

@section('content')
<div class="container py-5 user-page-wrapper">
    <h1 class="mb-4">Victoriile lui {{ $user->name ?? 'utilizator' }}</h1>

    <div class="card user-card shadow-sm">
        <h4 class="mb-3 px-3 pt-3">Toate victoriile</h4>

        @if(isset($wins) && $wins->count())
            <div class="table-responsive px-3 pb-3">
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width:40%">Melodie</th>
                            <th style="width:40%">Tema</th>
                            <th style="width:20%">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wins as $row)
                            <tr>
                                <td>{{ $row->song_title ?? '—' }}</td>
                                <td>{{ $row->theme_title ?: '—' }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->won_on)->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-3 pb-3">
                {{ $wins->links() }}
            </div>
        @else
            <div class="px-3 pb-3 text-muted">Nicio victorie încă.</div>
        @endif
    </div>
</div>
@endsection

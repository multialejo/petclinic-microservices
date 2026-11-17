@extends('layouts.app')

@section('title', 'Dueños')

@section('content')
    <h2>Dueños <small class="text-body-secondary fs-6">({{ $meta['total'] }} resultados)</small></h2>
    <div class="card"><div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Nombre</th><th>Dirección</th><th>Ciudad</th><th>Teléfono</th><th>Mascotas</th></tr></thead>
            <tbody>
            @foreach ($owners as $owner)
                <tr>
                    <td><a href="{{ route('owners.show', $owner['id']) }}">{{ $owner['first_name'] }} {{ $owner['last_name'] }}</a></td>
                    <td>{{ $owner['address'] }}</td>
                    <td>{{ $owner['city'] }}</td>
                    <td>{{ $owner['telephone'] }}</td>
                    <td>{{ collect($owner['pets'])->pluck('name')->join(', ') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
    <div class="mt-3">
        @include('partials.pager', ['meta' => $meta, 'route' => 'owners.index', 'query' => ['last_name' => $lastName]])
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('owners.find') }}">Nueva búsqueda</a>
@endsection

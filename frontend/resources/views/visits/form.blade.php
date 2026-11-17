@extends('layouts.app')

@section('title', 'Nueva visita')

@section('content')
    <h2>Nueva visita</h2>
    <div class="card mb-3"><div class="card-body">
        <table class="table table-sm mb-0">
            <thead><tr><th>Mascota</th><th>Nacimiento</th><th>Tipo</th><th>Dueño</th></tr></thead>
            <tbody><tr>
                <td>{{ $pet['name'] }}</td>
                <td>{{ $pet['birth_date'] }}</td>
                <td>{{ $pet['type']['name'] ?? '' }}</td>
                <td>{{ $pet['owner']['first_name'] }} {{ $pet['owner']['last_name'] }}</td>
            </tr></tbody>
        </table>
    </div></div>

    <div class="card mb-3"><div class="card-body">
        @error('pet_id')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <form method="POST" action="{{ route('visits.store', [$pet['owner_id'], $pet['id']]) }}" novalidate>
            @csrf
            @include('partials.field', ['name' => 'date', 'label' => 'Fecha', 'type' => 'date', 'value' => $defaultDate, 'attrs' => 'min="'.$defaultDate.'" required'])
            @include('partials.field', ['name' => 'description', 'label' => 'Descripción', 'attrs' => 'maxlength="255" required'])
            <button class="btn btn-primary" type="submit">Agendar visita</button>
            <a class="btn btn-link" href="{{ route('owners.show', $pet['owner_id']) }}">Cancelar</a>
        </form>
    </div></div>

    <h5>Visitas anteriores</h5>
    @if ($visitsUnavailable)
        <div class="alert alert-warning">El servicio de visitas no está disponible; no se puede mostrar el historial.</div>
    @else
        <table class="table table-sm">
            <thead><tr><th style="width: 9rem">Fecha</th><th>Descripción</th></tr></thead>
            <tbody>
            @forelse ($visits as $visit)
                <tr><td>{{ $visit['date'] }}</td><td>{{ $visit['description'] }}</td></tr>
            @empty
                <tr><td colspan="2" class="text-body-secondary">Sin visitas</td></tr>
            @endforelse
            </tbody>
        </table>
    @endif
@endsection

@extends('layouts.app')

@section('title', $owner['first_name'].' '.$owner['last_name'])

@section('content')
    <h2>Información del dueño</h2>
    <div class="card mb-3"><div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9 fw-semibold">{{ $owner['first_name'] }} {{ $owner['last_name'] }}</dd>
            <dt class="col-sm-3">Dirección</dt><dd class="col-sm-9">{{ $owner['address'] }}</dd>
            <dt class="col-sm-3">Ciudad</dt><dd class="col-sm-9">{{ $owner['city'] }}</dd>
            <dt class="col-sm-3">Teléfono</dt><dd class="col-sm-9">{{ $owner['telephone'] }}</dd>
        </dl>
    </div></div>
    <a class="btn btn-primary" href="{{ route('owners.edit', $owner['id']) }}">Editar dueño</a>
    <a class="btn btn-primary" href="{{ route('pets.create', $owner['id']) }}">Agregar mascota</a>

    <h2 class="mt-4">Mascotas y visitas</h2>

    @if ($visitsUnavailable)
        <div class="alert alert-warning">
            El servicio de visitas no está disponible en este momento. Se muestran los datos del dueño y
            sus mascotas; el historial de visitas aparecerá cuando el servicio se recupere.
        </div>
    @endif

    @forelse ($owner['pets'] as $pet)
        <div class="card mb-3"><div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <dl class="row mb-0">
                        <dt class="col-5">Nombre</dt><dd class="col-7">{{ $pet['name'] }}</dd>
                        <dt class="col-5">Nacimiento</dt><dd class="col-7">{{ $pet['birth_date'] }}</dd>
                        <dt class="col-5">Tipo</dt><dd class="col-7">{{ $pet['type']['name'] ?? '' }}</dd>
                    </dl>
                </div>
                <div class="col-md-8">
                    <table class="table table-sm mb-2">
                        <thead><tr><th style="width: 9rem">Fecha de visita</th><th>Descripción</th></tr></thead>
                        <tbody>
                        @unless ($visitsUnavailable)
                            @forelse ($visitsByPet->get($pet['id'], []) as $visit)
                                <tr><td>{{ $visit['date'] }}</td><td>{{ $visit['description'] }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-body-secondary">Sin visitas</td></tr>
                            @endforelse
                        @else
                            <tr><td colspan="2" class="text-body-secondary">No disponible</td></tr>
                        @endunless
                        </tbody>
                    </table>
                    <a href="{{ route('pets.edit', [$owner['id'], $pet['id']]) }}">Editar mascota</a>
                    · <a href="{{ route('visits.create', [$owner['id'], $pet['id']]) }}">Agregar visita</a>
                </div>
            </div>
        </div></div>
    @empty
        <p class="text-body-secondary">Este dueño aún no tiene mascotas.</p>
    @endforelse
@endsection

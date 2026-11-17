@extends('layouts.app')

@section('title', 'Servicio no disponible')

@section('content')
    <div class="alert alert-warning">
        <h4 class="alert-heading">Servicio no disponible</h4>
        <p class="mb-1">Uno de los microservicios necesarios para esta pantalla no responde. Inténtelo de nuevo en unos momentos.</p>
        @if (! empty($detail))<small class="text-body-secondary">{{ $detail }}</small>@endif
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('home') }}">Volver al inicio</a>
@endsection

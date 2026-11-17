@extends('layouts.app')

@section('title', 'No encontrado')

@section('content')
    <div class="alert alert-secondary">
        <h4 class="alert-heading">No encontrado</h4>
        <p class="mb-0">{{ $exception->getMessage() ?: 'El recurso solicitado no existe.' }}</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('owners.find') }}">Buscar dueños</a>
@endsection

@extends('layouts.app')

@section('title', 'Buscar dueños')

@section('content')
    <h2>Buscar dueños</h2>
    <div class="card"><div class="card-body">
        <form method="GET" action="{{ route('owners.index') }}" class="row g-2 align-items-start">
            <div class="col-sm-6">
                <label for="last_name" class="visually-hidden">Apellido</label>
                <input id="last_name" name="last_name" value="{{ old('last_name') }}" maxlength="30"
                       placeholder="Apellido (vacío = todos)"
                       class="form-control @error('last_name') is-invalid @enderror">
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" type="submit">Buscar</button>
            </div>
        </form>
    </div></div>
    <a class="btn btn-outline-secondary mt-3" href="{{ route('owners.create') }}">Registrar dueño</a>
@endsection

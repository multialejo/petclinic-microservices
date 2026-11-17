@extends('layouts.app')

@section('title', $pet ? 'Editar mascota' : 'Agregar mascota')

@section('content')
    <h2>{{ $pet ? 'Editar mascota' : 'Agregar mascota' }}</h2>
    <div class="card"><div class="card-body">
        <p><span class="text-body-secondary">Dueño:</span> <strong>{{ $owner['first_name'] }} {{ $owner['last_name'] }}</strong></p>
        <form method="POST" novalidate
              action="{{ $pet ? route('pets.update', [$owner['id'], $pet['id']]) : route('pets.store', $owner['id']) }}">
            @csrf
            @include('partials.field', ['name' => 'name', 'label' => 'Nombre', 'value' => $pet['name'] ?? '', 'attrs' => 'maxlength="30" required'])
            @include('partials.field', ['name' => 'birth_date', 'label' => 'Fecha de nacimiento', 'type' => 'date', 'value' => $pet['birth_date'] ?? '', 'attrs' => 'max="'.now()->toDateString().'" required'])
            <div class="mb-3">
                <label for="type_id" class="form-label">Tipo</label>
                @php($selected = (string) old('type_id', $pet['type']['id'] ?? ''))
                <select id="type_id" name="type_id" class="form-select @error('type_id') is-invalid @enderror" required>
                    <option value="" disabled @selected($selected === '')>Seleccione…</option>
                    @foreach ($types as $type)
                        <option value="{{ $type['id'] }}" @selected($selected === (string) $type['id'])>{{ $type['name'] }}</option>
                    @endforeach
                </select>
                @error('type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button class="btn btn-primary" type="submit">{{ $pet ? 'Actualizar mascota' : 'Agregar mascota' }}</button>
            <a class="btn btn-link" href="{{ route('owners.show', $owner['id']) }}">Cancelar</a>
        </form>
    </div></div>
@endsection

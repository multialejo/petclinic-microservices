@extends('layouts.app')

@section('title', $owner ? 'Editar dueño' : 'Registrar dueño')

@section('content')
    <h2>{{ $owner ? 'Editar dueño' : 'Registrar dueño' }}</h2>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ $owner ? route('owners.update', $owner['id']) : route('owners.store') }}" novalidate>
            @csrf
            <div class="row">
                <div class="col-md-6">@include('partials.field', ['name' => 'first_name', 'label' => 'Nombre', 'value' => $owner['first_name'] ?? '', 'attrs' => 'maxlength="30" required'])</div>
                <div class="col-md-6">@include('partials.field', ['name' => 'last_name', 'label' => 'Apellido', 'value' => $owner['last_name'] ?? '', 'attrs' => 'maxlength="30" required'])</div>
            </div>
            @include('partials.field', ['name' => 'address', 'label' => 'Dirección', 'value' => $owner['address'] ?? '', 'attrs' => 'maxlength="255" required'])
            <div class="row">
                <div class="col-md-6">@include('partials.field', ['name' => 'city', 'label' => 'Ciudad', 'value' => $owner['city'] ?? '', 'attrs' => 'maxlength="80" required'])</div>
                <div class="col-md-6">@include('partials.field', ['name' => 'telephone', 'label' => 'Teléfono (10 dígitos)', 'type' => 'tel', 'value' => $owner['telephone'] ?? '', 'attrs' => 'inputmode="numeric" required'])</div>
            </div>
            <button class="btn btn-primary" type="submit">{{ $owner ? 'Actualizar dueño' : 'Registrar dueño' }}</button>
            <a class="btn btn-link" href="{{ $owner ? route('owners.show', $owner['id']) : route('owners.find') }}">Cancelar</a>
        </form>
    </div></div>
@endsection

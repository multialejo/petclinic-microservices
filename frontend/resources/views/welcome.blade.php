@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <div class="row align-items-center g-4">
        <div class="col-md-7">
            <h2>Bienvenido a PetClinic</h2>
            <p class="lead">Gestión de dueños, mascotas, visitas y veterinarios.</p>
            <p class="text-body-secondary">
                Esta interfaz reemplaza las vistas Thymeleaf del monolito Spring. No tiene base de datos:
                cada pantalla se arma con llamadas al API Gateway, que enruta a los microservicios
                <code>ms-customers</code>, <code>ms-visits</code> y <code>ms-vets</code>.
            </p>
            <a class="btn btn-primary" href="{{ route('owners.find') }}">Buscar dueños</a>
            <a class="btn btn-outline-secondary" href="{{ route('vets.index') }}">Ver veterinarios</a>
        </div>
        <div class="col-md-5 text-center display-1">🐶🐱🦎</div>
    </div>
@endsection

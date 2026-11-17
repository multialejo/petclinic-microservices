@extends('layouts.app')

@section('title', 'Veterinarios')

@section('content')
    <h2>Veterinarios</h2>
    <div class="card"><div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Nombre</th><th>Especialidades</th></tr></thead>
            <tbody>
            @foreach ($vets as $vet)
                <tr>
                    <td>{{ $vet['first_name'] }} {{ $vet['last_name'] }}</td>
                    <td>{{ collect($vet['specialties'])->pluck('name')->join(', ') ?: 'ninguna' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
    <div class="mt-3">@include('partials.pager', ['meta' => $meta, 'route' => 'vets.index'])</div>
@endsection

@extends('layouts.app', ['menu' => 'vets'])

@section('content')
  <h2>Veterinarians</h2>

  <table id="vets" class="table table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Specialties</th>
      </tr>
    </thead>
    <tbody>
@foreach ($vets as $vet)
      <tr>
        <td>{{ $vet['first_name'] }} {{ $vet['last_name'] }}</td>
        <td>
          @foreach ($vet['specialties'] as $specialty)<span>{{ $specialty['name'] }} </span>@endforeach @if (empty($vet['specialties']))<span>none</span>@endif
        </td>
      </tr>
@endforeach
    </tbody>
  </table>
@include('partials.pager', ['meta' => $meta, 'url' => '/vets.html'])
@endsection

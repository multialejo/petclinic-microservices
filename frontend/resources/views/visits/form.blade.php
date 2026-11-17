@extends('layouts.app', ['menu' => 'owners'])

@section('content')
  <h2>
    New
    Visit
  </h2>

  <b>Pet</b>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Birth Date</th>
        <th>Type</th>
        <th>Owner</th>
      </tr>
    </thead>
    <tr>
      <td>{{ $pet['name'] }}</td>
      <td>{{ $pet['birth_date'] }}</td>
      <td>{{ $pet['type']['name'] ?? '' }}</td>
      <td>{{ $pet['owner']['first_name'] }} {{ $pet['owner']['last_name'] }}</td>
    </tr>
  </table>

  <form class="form-horizontal" method="post">
    @csrf
    <div class="form-group has-feedback">
@include('partials.input', ['label' => 'Date', 'name' => 'date', 'type' => 'date', 'value' => $defaultDate, 'min' => $defaultDate])
@include('partials.input', ['label' => 'Description', 'name' => 'description', 'type' => 'text'])
    </div>

    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <input type="hidden" name="petId" value="{{ $pet['id'] }}" />
        <button class="btn btn-primary" type="submit">Add Visit</button>
      </div>
    </div>
  </form>

  <br />
  <b>Previous Visits</b>
  <table class="table table-striped">
    <tr>
      <th>Date</th>
      <th>Description</th>
    </tr>
@foreach ($visits as $visit)
    <tr>
      <td>{{ $visit['date'] }}</td>
      <td>{{ $visit['description'] }}</td>
    </tr>
@endforeach
  </table>
@endsection

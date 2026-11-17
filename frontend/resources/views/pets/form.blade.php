@extends('layouts.app', ['menu' => 'owners'])

@section('content')
  <h2>
@unless ($pet)
    New
@endunless
    <span>Pet</span>
  </h2>
  <form class="form-horizontal" method="post">
    @csrf
    <input type="hidden" name="id" value="{{ $pet['id'] ?? '' }}" />
    <div class="form-group has-feedback">
      <div class="form-group">
        <label class="col-sm-2 control-label">Owner</label>
        <div class="col-sm-10">
          <span>{{ $owner['first_name'] }} {{ $owner['last_name'] }}</span>
        </div>
      </div>
@include('partials.input', ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'value' => $pet['name'] ?? ''])
@include('partials.input', ['label' => 'Birth Date', 'name' => 'birthDate', 'type' => 'date', 'value' => $pet['birth_date'] ?? ''])
@include('partials.select', ['label' => 'Type', 'name' => 'type', 'items' => $types, 'selected' => $pet['type']['name'] ?? ''])
    </div>
    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button class="btn btn-primary" type="submit">{{ $pet ? 'Update Pet' : 'Add Pet' }}</button>
      </div>
    </div>
  </form>
@endsection

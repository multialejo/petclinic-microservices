@extends('layouts.app', ['menu' => 'owners'])

@section('content')
  <h2>Owner</h2>
  <form class="form-horizontal" id="add-owner-form" method="post">
    @csrf
    <div class="form-group has-feedback">
@include('partials.input', ['label' => 'First Name', 'name' => 'firstName', 'type' => 'text', 'value' => $owner['first_name'] ?? ''])
@include('partials.input', ['label' => 'Last Name', 'name' => 'lastName', 'type' => 'text', 'value' => $owner['last_name'] ?? ''])
@include('partials.input', ['label' => 'Address', 'name' => 'address', 'type' => 'text', 'value' => $owner['address'] ?? ''])
@include('partials.input', ['label' => 'City', 'name' => 'city', 'type' => 'text', 'value' => $owner['city'] ?? ''])
@include('partials.input', ['label' => 'Telephone', 'name' => 'telephone', 'type' => 'text', 'value' => $owner['telephone'] ?? ''])
    </div>
    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button class="btn btn-primary" type="submit">{{ $owner ? 'Update Owner' : 'Add Owner' }}</button>
      </div>
    </div>
  </form>
@endsection

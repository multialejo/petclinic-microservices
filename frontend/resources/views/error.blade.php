{{-- Port de templates/error.html. Params: status, message? --}}
@extends('layouts.app', ['menu' => 'error'])

@section('content')
  <img src="/resources/images/pets.png" />
  <!-- Title: Something happened -->
  <h2>Something happened...</h2>

  <!-- Status-specific error message -->
  <p>
@switch($status)
@case(404)
    <span>The requested page was not found.</span>
@break
@case(500)
    <span>An internal server error occurred.</span>
@break
@default
    <span>An unexpected error occurred.</span>
@endswitch
  </p>

  <!-- Exception message (for developers) -->
  <p>{{ $message ?? '' }}</p>
@endsection

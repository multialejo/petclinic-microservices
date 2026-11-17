@extends('layouts.app', ['menu' => 'owners'])

@section('content')
  <h2>Owners</h2>

  <table id="owners" class="table table-striped">
    <thead>
      <tr>
        <th style="width: 150px;">Name</th>
        <th style="width: 200px;">Address</th>
        <th>City</th>
        <th style="width: 120px">Telephone</th>
        <th>Pets</th>
      </tr>
    </thead>
    <tbody>
@foreach ($owners as $owner)
      <tr>
        <td>
          <a href="/owners/{{ $owner['id'] }}">{{ $owner['first_name'] }} {{ $owner['last_name'] }}</a>
        </td>
        <td>{{ $owner['address'] }}</td>
        <td>{{ $owner['city'] }}</td>
        <td>{{ $owner['telephone'] }}</td>
        <td><span>{{ collect($owner['pets'])->pluck('name')->join(', ') }}</span></td>
      </tr>
@endforeach
    </tbody>
  </table>
@include('partials.pager', ['meta' => $meta, 'url' => '/owners', 'query' => ['lastName' => $lastName]])
@endsection

{{-- Port 1:1 de spring-petclinic templates/fragments/layout.html. Uso: @extends('layouts.app', ['menu' => 'owners']) --}}
<!DOCTYPE html>

<html>

<head>

  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" type="image/x-icon" href="/resources/images/favicon.png">
  <title>PetClinic :: a Spring Framework demonstration</title>
  <link href="/webjars/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/resources/css/petclinic.css" />

</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark" role="navigation">
    <div class="container-fluid">
      <a class="navbar-brand" href="/"><span></span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-navbar">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="main-navbar" style>

        <ul class="nav navbar-nav me-auto">
@foreach ([
    ['/', 'home', 'home page', 'home', 'Home'],
    ['/owners/find', 'owners', 'find owners', 'search', 'Find Owners'],
    ['/vets.html', 'vets', 'veterinarians', 'th-list', 'Veterinarians'],
    ['/oups', 'error', 'trigger a RuntimeException to see how it is handled', 'exclamation-triangle', 'Error'],
] as [$link, $active, $title, $glyph, $text])
          <li class="nav-item">
            <a class="{{ $active === ($menu ?? '') ? 'nav-link active' : 'nav-link' }}" href="{{ $link }}" title="{{ $title }}">
              <span class="fa fa-{{ $glyph }}"></span>
              <span>{{ $text }}</span>
            </a>
          </li>
@endforeach

        </ul>
      </div>
    </div>
  </nav>
  <div class="container-fluid">
    <div class="container xd-container">

      <body>
@yield('content')
</body>

      <br />
      <br />
      <div class="container">
        <div class="row">
          <div class="col-12 text-center">
            <img src="/resources/images/spring-logo.svg"
              alt="VMware Tanzu Logo" class="logo">
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="/webjars/bootstrap/5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PetClinic') · PetClinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --pc-green: #6db33f; --pc-dark: #34302d; }
        body { background: #f5f5f3; }
        .navbar { background: var(--pc-dark); border-bottom: 4px solid var(--pc-green); }
        .navbar .nav-link.active { color: #fff; border-bottom: 2px solid var(--pc-green); }
        h2 { color: var(--pc-dark); font-weight: 600; margin-bottom: 1.25rem; }
        .btn-primary { --bs-btn-bg: var(--pc-green); --bs-btn-border-color: var(--pc-green);
                       --bs-btn-hover-bg: #5a9a32; --bs-btn-hover-border-color: #5a9a32; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        footer { color: #888; font-size: .85rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">🐾 PetClinic</a>
        <div class="navbar-nav">
            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a>
            <a class="nav-link {{ request()->routeIs('owners.*', 'pets.*', 'visits.*') ? 'active' : '' }}" href="{{ route('owners.find') }}">Buscar dueños</a>
            <a class="nav-link {{ request()->routeIs('vets.*') ? 'active' : '' }}" href="{{ route('vets.index') }}">Veterinarios</a>
        </div>
    </div>
</nav>

<main class="container">
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @yield('content')
</main>

<footer class="container py-4 mt-4 text-center">
    Frontend desacoplado (Laravel + Blade, sin base de datos) · consume solo el API Gateway
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

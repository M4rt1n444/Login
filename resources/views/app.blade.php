<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mi Aplicación')</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f3f4f6; margin: 0; }
        .card { max-width: 400px; margin: 60px auto; background: #fff; padding: 32px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        h1 { margin-top: 0; font-size: 1.4rem; }
        label { display: block; margin: 14px 0 4px; font-weight: 600; font-size: .9rem; }
        input { width: 100%; padding: 9px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; margin-top: 20px; padding: 10px; background: #2563eb; color: #fff; border: 0; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        .error { color: #b91c1c; font-size: .85rem; margin-top: 4px; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .alert-ok { background: #dcfce7; color: #166534; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .link { text-align: center; margin-top: 16px; font-size: .9rem; }
    </style>
</head>
<body>
    <div class="card">
        @yield('content')
    </div>
</body>
</html>

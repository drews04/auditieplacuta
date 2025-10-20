
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Auditie Placuta</title>

  
  <link rel="stylesheet" href="{{ asset('assets/css/register.css') }}?v={{ filemtime(public_path('assets/css/register.css')) }}">
</head>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auditie Placuta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="guest-body"> {{-- THIS CLASS IS CRITICAL --}}
    @yield('content')
</body>
</html>
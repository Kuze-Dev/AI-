<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'AI Widget' }}</title>

    @livewireStyles
</head>
<body class="antialiased bg-gray-100">
    {{ $slot }}
    @livewireScripts
</body>
</html>

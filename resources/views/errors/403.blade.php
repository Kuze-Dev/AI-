<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Halcyon Laravel | Tall Boilerplate</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans&display=swap" rel="stylesheet">

    @vite('resources/css/web/app.css')
</head>

<body>
    <div class="flex items-center justify-center h-screen bg-gradient-to-br from-gray-200 to-gray-600">
        {{-- <div class="border rounded-lg w-14 h-12 bg-gray-700 animate-dip">
          <div class="absolute border-l border-gray-700 h-5 w-3 left-1/2 top-[-30px]"></div>
          <div class="absolute border-r border-transparent h-5 animate-spin left-1/2 top-[-10px]"></div>
        </div> --}}
        <div class="text-center mx-4">
          <h1 class="my-10">Access to this page is restricted</h1>
          <p>Please check with the site admin if you believe this is a mistake.</p>
        </div>
      </div>
</body>

  

</html>

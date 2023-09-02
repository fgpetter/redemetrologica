<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-sidebar="dark"
    data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-bs-theme="light">

<head>

    <meta charset="utf-8" />
    <title> @yield('title') | Rede Metrológica RS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/faviconV2.PNG') }}">

    {{-- font wansome --}}
    <script src="https://kit.fontawesome.com/02f4ca9b8a.js" crossorigin="anonymous"></script>

    {{-- Glide.js --}}
    <!-- Required Core Stylesheet -->
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/css/glide.core.min.css">
    <!-- Optional Theme Stylesheet -->
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/css/glide.theme.min.css">

    @include('layouts.head-css')



</head>

@yield('content')

@include('layouts.vendor-scripts')
</body>

</html>

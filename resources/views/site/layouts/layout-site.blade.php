<!doctype html>
<html lang="pt-BR" data-layout="vertical" data-sidebar="dark"
    data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-bs-theme="light">

<head>

    <meta charset="utf-8" />
    <title> @yield('title') | Rede Metrológica RS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="adopt-website-id" content="508299ca-d188-4c27-a881-d9f42be72dbd" />
    <script src="//tag.goadopt.io/injector.js?website_code=508299ca-d188-4c27-a881-d9f42be72dbd" 
    class="adopt-injector"></script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.png') }}">

    {{-- font wansome --}}
    <script src="https://kit.fontawesome.com/02f4ca9b8a.js" crossorigin="anonymous"></script>

    @vite(['resources/scss/bootstrap.scss', 'resources/scss/icons.scss', 'resources/scss/app.scss', 'resources/scss/custom.scss'])

    @include('site.layouts.head-css')



</head>

<body>
    <x-site.nav />
    @yield('content')

    <x-site.footer />

    @include('site.layouts.vendor-scripts')
</body>

</html>

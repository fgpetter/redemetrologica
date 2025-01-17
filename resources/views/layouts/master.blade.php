<!doctype html>
<html lang="pt-BR" data-layout="vertical" data-sidebar="dark"
    data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-bs-theme="light" data-topbar="light">

<head>
    <meta charset="utf-8" />
    <title> @yield('title') | Admin Rede Metrológica RS </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('build/images/favicon.png') }}">
    {{-- sweetalert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('layouts.head-css')

    {{-- vite hot refresh --}}
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    @livewireStyles
</head>

{{-- @section('body') --}}

<body>
    {{-- @show --}}
    <x-alerts.alert />
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    @yield('content')
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
    @livewireScripts
</body>

</html>

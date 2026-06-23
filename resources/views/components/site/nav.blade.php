<nav class="navbar navbar-expand-lg sticky-top SiteHeader border-bottom">
    <div class="container">
        <a class="navbar-brand py-1" href="/home">
            <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" alt="Rede Metrológica RS" height="95">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav nav-underline fs-sm-6 fs-xxl-5 fw-bold">

                <li class="nav-item">
                    <a class="nav-link d-none d-xl-block" href="/associe-se">Associe-se</a>
                </li>

                <x-site.nav.link nome='Cursos' link='cursos' />

                <x-site.nav.link nome='Ensaios de Proficiência' link='interlaboratoriais' />

                <x-site.nav.link_dropdown />

                <x-site.nav.link nome='Fale Conosco' link='fale-conosco' />
                <x-site.nav.link nome='Downloads' link='laboratorios-downloads' />
                
                <li class="nav-item">
                    @if( auth()->check() )
                        <a class="btn btn-info px-4"  href="/painel"> Painel </a>
                    @else
                        <a class="btn btn-info px-4"  href="/painel"> Login </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

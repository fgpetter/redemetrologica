<nav class="navbar navbar-expand-lg sticky-top py-3 SiteHeader border-bottom">
    <div class="container">
        <a class="navbar-brand" href="home">
            <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" alt="Rede Metrológica RS" height="95">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav nav-underline h5">

                {{-- @foreach ($menu as $menu_item)
                    <x-nav.link nome='{{$menu_item->nome}}' link='{{$menu_item->item}}'/>
                @endforeach --}}

                <x-site.nav.link nome='Notícias' link='noticias' />

                <x-site.nav.link nome='Associe-se' link='associe-se' />

                <x-site.nav.link nome='Cursos' link='cursos' />

                <x-site.nav.link nome='Interlaboratoriais' link='interlaboratoriais' />

                <x-site.nav.link_dropdown />

                <x-site.nav.link nome='Fale Conosco' link='fale-conosco' />
            </ul>
        </div>
    </div>
</nav>

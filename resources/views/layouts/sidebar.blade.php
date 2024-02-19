<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
  <!-- LOGO -->
  <div class="navbar-brand-box">
    <a href="index" class="logo logo-dark">
      <span class="logo-sm">
        <img src="{{ URL::asset('build/images/favicon.png') }}" alt="" height="32">
      </span>
      <span class="logo-lg">
        <img src="{{ URL::asset('build/images/site/LOGO_REDE_BRANCO.png') }}" alt="" height="32">
      </span>
    </a>
    <a href="index" class="logo logo-light">
      <span class="logo-sm">
        <img src="{{ URL::asset('build/images/favicon.png') }}" alt="" height="32">
      </span>
      <span class="logo-lg">
        <img src="{{ URL::asset('build/images/site/LOGO_REDE_BRANCO.png') }}" alt="" height="32">
      </span>
    </a>
    <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover"
      id="vertical-hover">
      <i class="ri-record-circle-line"></i>
    </button>
  </div>
  <div id="scrollbar">
    <div class="container-fluid">

      <div id="two-column-menu">
      </div>
      <ul class="navbar-nav" id="navbar-nav">
        {{-- // ADMINISTRAÇÃO // --}}
        <li class="menu-title"><span>ADMINISTRAÇÃO</span></li>

        {{-- Usuarios --}}
        <li class="nav-item"></li>
          <a class="nav-link menu-link collapsed {{ request()->is('painel/user/*') ? 'active' : '' }}"
            href="#sidebarUsers" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ request()->is('painel/user/*') ? 'true' : 'false' }}"
            aria-controls="sidebarUsers">
            <i class="ph-user-circle"></i> USUÁRIOS
          </a>
          <div class="collapse menu-dropdown {{ request()->is('painel/user/*') ? 'show' : '' }}"
            id="sidebarUsers">
            <ul class="nav nav-sm flex-column">
              <li class="nav-item">
                <a href="/painel/user/index"
                  class="nav-link {{ request()->is('painel/user/index') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Listar
                </a>
              </li>
            </ul>
          </div>
        </li>

        {{-- Pessoas --}}
        <li class="nav-item">
          <a class="nav-link menu-link collapsed {{ in_array(request()->route()->getPrefix(), ['painel/funcionario', 'painel/pessoa', 'painel/avaliador', 'painel/instrutor']) ? 'active' : '' }}"
            href="#sidebarPessoas" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/funcionario', 'painel/pessoa', 'painel/avaliador', 'painel/instrutor']) ? 'true' : 'false' }}"
            aria-controls="sidebarPessoas">
            <i class="ph-identification-card"></i> PESSOAS
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/funcionario', 'painel/pessoa', 'painel/avaliador', 'painel/avaliador', 'painel/instrutor']) ? 'show' : '' }}"
            id="sidebarPessoas">
            <ul class="nav nav-sm flex-column">
              <li class="nav-item">
                <a href="{{ route('pessoa-index') }}"
                  class="nav-link {{ request()->is('painel/pessoa/index') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Pessoas
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('funcionario-index') }}"
                  class="nav-link {{ request()->is('painel/funcionario/index') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Funcionários
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('avaliador-index') }}"
                  class="nav-link {{ request()->is('painel/avaliador/index') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Avaliadores
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('instrutor-index') }}"
                  class="nav-link {{ request()->is('painel/instrutor/index') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Instrutores
                </a>
              </li>

            </ul>
          </div>
        </li>

        {{-- Cursos --}}
        <li class="nav-item">
          <a class="nav-link menu-link collapsed {{ in_array(request()->route()->getPrefix(), ['painel/curso', 'painel/agendamento-curso']) ? 'active' : '' }}"
            href="#sidebarCursos" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/curso', 'painel/agendamento-curso']) ? 'true' : 'false' }}"
            aria-controls="sidebarCursos">
            <i class="ph-identification-card"></i>CURSOS
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/curso', 'painel/agendamento-curso']) ? 'show' : '' }}"
            id="sidebarCursos">
            <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              <a href="{{ route('curso-index') }}"
                class="nav-link {{ request()->is('painel/curso/index') ? 'active' : '' }}"
                role="button" data-key="t-signin">
                Cursos
              </a>
              </li>
              <li class="nav-item">
              <a href="{{ route('agendamento-curso-index') }}"
                class="nav-link {{ request()->is('painel/agendamento-curso/index') ? 'active' : '' }}"
                role="button" data-key="t-signin">
                Agendamento de Cursos
              </a>
              </li>
            </ul>
          </div>
        </li>

        {{-- CADASTROS ADICIONAIS --}}
        <li class="nav-item">
          <a class="nav-link collapsed {{ in_array(request()->route()->getPrefix(), ['painel/bancos', 'painel/area-atuacao', 'painel/materiais-padroes', 'painel/parametros', 'painel/tipos-avaliacao']) ? 'active' : '' }}"
            href="#sidebarCadastrosAdicionais" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/bancos', 'painel/area-atuacao', 'painel/materiais-padroes', 'painel/parametros', 'painel/tipos-avaliacao']) ? 'true' : 'false' }}"
            aria-controls="sidebarCadastrosAdicionais">
            <i class="ph-list"></i> <span>CADASTROS ADICIONAIS</span>
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/bancos', 'painel/area-atuacao', 'painel/materiais-padroes', 'painel/parametros', 'painel/tipos-avaliacao']) ? 'show' : '' }}"
            id="sidebarCadastrosAdicionais">
            <ul class="nav nav-sm flex-column">
              <li class="nav-item">
              <a href="{{ route('area-atuacao-index') }}"
                class="nav-link {{ request()->is('painel/area-atuacao/index') ? 'active' : '' }}"
                role="button" data-key="t-area-atuacao">
                Áreas de atuação
              </a>
              </li>
              <li class="nav-item">
              <a href="{{ route('materiais-padroes-index') }}"
                class="nav-link {{ request()->is('painel/materiais-padroes/index') ? 'active' : '' }}"
                role="button" data-key="t-materiais-padroes">
                Materiais Padrões
              </a>
              </li>
              <li class="nav-item">
              <a href="{{ route('parametros-index') }}"
                class="nav-link {{ request()->is('painel/parametros/index') ? 'active' : '' }}"
                role="button" data-key="t-parametros">
                Parâmetros
              </a>
              </li>
              <li class="nav-item">
              <a href="{{ route('tipo-avaliacao-index') }}"
                class="nav-link {{ request()->is('painel/tipos-avaliacao/index') ? 'active' : '' }}"
                role="button" data-key="t-tipos-avaliacao">
                Tipos de avaliação
              </a>
              </li>

              <hr class="border-white me-4 my-2">
              
              <li class="nav-item">
                <a href="{{ route('banco-index') }}"
                  class="nav-link {{ request()->is('painel/bancos/index') ? 'active' : '' }}"
                  role="button" data-key="t-bancos">
                  Bancos
                </a>
                </li>
            </ul>
          </div>
        </li>


        {{-- // SITE // --}}
        <li class="menu-title"><span>SITE</span></li>

        {{-- Notícias --}}
        <li class="nav-item">
        <a href="/painel/post/noticias"
          class="nav-link {{ request()->is('painel/post/noticias/index') ? 'active' : '' }}"
          role="button" data-key="t-signin">
          <i class="ph-newspaper"></i> <span>NOTÍCIAS</span>
        </a>
        </li>
        {{-- Galeria --}}
        <li class="nav-item">
        <a href="/painel/post/galeria" 
          class="nav-link {{ request()->is('painel/post/galeria/index') ? 'active' : '' }}"
          role="button" data-key="t-signin">
          <i class="ph-image"></i> <span>GALERIA</span>
        </a>
        </li>

      </ul>
    </div>
    <!-- Sidebar -->
  </div>

  <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
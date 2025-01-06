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


      @canany(['admin','funcionario'])

        {{-- // ADMINISTRAÇÃO // --}}
        <li class="menu-title"><span>ADMINISTRAÇÃO</span></li>
        

        @can(['admin'])
        {{-- Usuarios --}}
        <li class="nav-item">
          <a class="nav-link menu-link collapsed {{ in_array(request()->route()->getPrefix(), ['painel/user']) ? 'active' : '' }}"
            href="#sidebarUsers" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/user']) ? 'true' : 'false' }}"
            aria-controls="sidebarUsers">
            <i class="ph-user-circle"></i> <span>USUÁRIOS</span>
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/user']) ? 'show' : '' }}" 
            id="sidebarUsers">
            <ul class="nav nav-sm flex-column">
              <li class="nav-item">
                <a href="{{ route('user-index') }}"
                  class="nav-link {{ request()->is('painel/user/index') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Listar
                </a>
              </li>
            </ul>
          </div>
        </li>
        @endcan

        {{-- Pessoas --}}
        <li class="nav-item">
          <a class="nav-link menu-link collapsed "
            href="#sidebarPessoas" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/funcionario', 'painel/pessoa', 'painel/avaliador', 'painel/instrutor', 'painel/laboratorios', 'painel/fornecedor']) ? 'true' : 'false' }}"
            aria-controls="sidebarPessoas">
            <i class="ph-address-book"></i> <span>PESSOAS</span>
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/funcionario', 'painel/pessoa', 'painel/avaliador', 'painel/instrutor', 'painel/laboratorios', 'painel/fornecedor']) ? 'show' : '' }}"
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
                  class="nav-link {{ request()->is('painel/funcionario/*') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Funcionários
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('avaliador-index') }}"
                  class="nav-link {{ request()->is('painel/avaliador/*') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Avaliadores
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('laboratorio-index') }}"
                  class="nav-link {{ request()->is('painel/laboratorios/*') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Laboratórios
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('instrutor-index') }}"
                  class="nav-link {{ request()->is('painel/instrutor/*') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Instrutores
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('fornecedor-index') }}"
                  class="nav-link {{ request()->is('painel/fornecedor/*') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Fornecedores
                </a>
              </li>

            </ul>
          </div>
        </li>
        @canany(['admin','avaliacoes'])
        {{-- Avaliacoes --}}
        <li class="nav-item">
          <a class="nav-link menu-link collapsed {{ in_array(request()->route()->getPrefix(), ['painel/avaliacao']) ? 'active' : '' }}"
            href="#sidebarAvaliacoes" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/avaliacao']) ? 'true' : 'false' }}"
            aria-controls="sidebarAvaliacoes">
            <i class="ph-flask"></i> <span>AVALIAÇÕES</span> 
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/avaliacao']) ? 'show' : '' }}"
            id="sidebarAvaliacoes">
            <ul class="nav nav-sm flex-column">
              <li class="nav-item">
                <a href="{{ route('agendamento-avaliacao-index') }}"
                  class="nav-link {{ request()->is('painel/avaliacao/*') ? 'active' : '' }}"
                  role="button" data-key="t-signin">
                  Agendamento de Avaliações
                </a>
              </li>
            </ul>
          </div>
        </li>
        @endcan

        @canany(['admin','cursos'])
        {{-- Cursos --}}
        <li class="nav-item">
          <a class="nav-link menu-link collapsed {{ in_array(request()->route()->getPrefix(), ['painel/curso', 'painel/agendamento-curso']) ? 'active' : '' }}"
            href="#sidebarCursos" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/curso', 'painel/agendamento-curso']) ? 'true' : 'false' }}"
            aria-controls="sidebarCursos">
            <i class="ph-books-thin"></i> <span>CURSOS</span> 
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/curso', 'painel/agendamento-curso']) ? 'show' : '' }}"
            id="sidebarCursos">
            <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              <a href="{{ route('curso-index') }}"
                class="nav-link {{ request()->is('painel/curso/*') ? 'active' : '' }}"
                role="button" data-key="t-signin">
                Cursos
              </a>
              </li>
              <li class="nav-item">
              <a href="{{ route('agendamento-curso-index') }}"
                class="nav-link {{ request()->is('painel/agendamento-curso/*') ? 'active' : '' }}"
                role="button" data-key="t-signin">
                Agendamento de Cursos
              </a>
              </li>
            </ul>
          </div>
        </li>
        @endcan

        @canany(['admin','interlabs'])
        {{-- Interlabs --}}
        <li class="nav-item">
          <a class="nav-link menu-link collapsed {{ in_array(request()->route()->getPrefix(), ['painel/interlab', 'painel/agendamento-interlab']) ? 'active' : '' }}"
            href="#sidebarInterlabs" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/interlab', 'painel/agendamento-interlab']) ? 'true' : 'false' }}"
            aria-controls="sidebarInterlabs">
            <i class="ph-graph-thin"></i> <span>INTERLABS</span> 
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/interlab', 'painel/agenda-interlab']) ? 'show' : '' }}"
            id="sidebarInterlabs">
            <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              <a href="{{ route('interlab-index') }}"
                class="nav-link {{ request()->is('painel/interlab/*') ? 'active' : '' }}"
                role="button" data-key="t-signin">
                Interlab
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('agenda-interlab-index') }}"
                class="nav-link {{ request()->is('painel/agenda-interlab/*') ? 'active' : '' }}" 
                role="button" data-key="t-signin">
                Agendamento de Interlabs
              </a>
            </li>
            </ul>
          </div>
        </li>
        @endcan

        @canany(['admin','financeiro'])
        {{-- FINANCEIRO --}}
        <li class="nav-item">
          <a class="nav-link menu-link collapsed {{ in_array(request()->route()->getPrefix(), ['painel/financeiro']) ? 'active' : '' }}"
            href="#sidebarFinanceiro" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/financeiro']) ? 'true' : 'false' }}"
            aria-controls="sidebarFinanceiro">
            <i class="ph-calculator"></i> <span>FINANCEIRO</span> 
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/financeiro']) ? 'show' : '' }}"
            id="sidebarFinanceiro">
            <ul class="nav nav-sm flex-column">
              <li class="nav-item">
              <a href="{{ route('a-receber-index') }}"
                class="nav-link {{ request()->is('painel/financeiro/areceber/index') ? 'active' : '' }}"
                role="button" data-key="t-signin">
                A receber
              </a>
              </li>
              <li class="nav-item">
              <a href="{{ route('lancamento-financeiro-index') }}"
                class="nav-link {{ request()->is('painel/financeiro/lancamento/index') ? 'active' : '' }}"
                role="button" data-key="t-signin">
                Lançamentos
              </a>
              </li>
            </ul>
          </div>
        </li>
        @endcan

        {{-- CADASTROS ADICIONAIS --}}
        <li class="nav-item">
          <a class="nav-link menu-link collapsed {{ in_array(request()->route()->getPrefix(), ['painel/plano-conta', 'painel/modalidade-pagamento', 'painel/centro-custo', 'painel/banco', 'painel/area-atuacao', 'painel/materiais-padroes', 'painel/parametros', 'painel/tipos-avaliacao']) ? 'active' : '' }}"
            href="#sidebarCadastrosAdicionais" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ in_array(request()->route()->getPrefix(), ['painel/plano-conta', 'painel/modalidade-pagamento', 'painel/centro-custo', 'painel/banco', 'painel/area-atuacao', 'painel/materiais-padroes', 'painel/parametros', 'painel/tipos-avaliacao']) ? 'true' : 'false' }}"
            aria-controls="sidebarCadastrosAdicionais">
            <i class="ph-list"></i> <span>CADASTROS ADICIONAIS</span>
          </a>
          <div class="collapse menu-dropdown {{ in_array(request()->route()->getPrefix(), ['painel/plano-conta', 'painel/modalidade-pagamento', 'painel/centro-custo', 'painel/banco', 'painel/area-atuacao', 'painel/materiais-padroes', 'painel/parametros', 'painel/tipos-avaliacao']) ? 'show' : '' }}"
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

              <hr class="border-white me-4 my-1">
              
              <li class="nav-item">
                <a href="{{ route('banco-index') }}"
                  class="nav-link {{ request()->is('painel/banco/index') ? 'active' : '' }}"
                  role="button" data-key="t-banco">
                  Bancos
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('centro-custo-index') }}"
                  class="nav-link {{ request()->is('painel/centro-custo/index') ? 'active' : '' }}"
                  role="button" data-key="t-centro-custo">
                  Centro de custo
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('modalidade-pagamento-index') }}"
                  class="nav-link {{ request()->is('painel/modalidade-pagamento/index') ? 'active' : '' }}"
                  role="button" data-key="t-modalidade-pagamento">
                  Modalidades
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('plano-conta-index') }}"
                  class="nav-link {{ request()->is('painel/plano-conta/index') ? 'active' : '' }}"
                  role="button" data-key="t-plano-conta">
                  Plano de contas
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

        {{-- Downloads --}}
        <li class="nav-item">
          <a href="/painel/downloads/index" 
            class="nav-link {{ request()->is('painel/downloads/index') ? 'active' : '' }}"
            role="button" data-key="t-signin">
            <i class="ph-file-arrow-down"></i> <span>Arquivos de Downloads</span>
          </a>
        </li>

      @endcan

      @can('cliente')
        {{-- // MENU DO CLIENTE // --}}
        <li class="menu-title"><span>Área do Cliente</span></li>
        <li class="nav-item">
          <a href="/painel" class="nav-link {{ request()->is('painel') ? 'active' : '' }}"
            role="button" data-key="t-signin"> Painel </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link {{ request()->is('#') ? 'active' : '' }}"
            role="button" data-key="t-signin"> Meus Cursos </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link {{ request()->is('#') ? 'active' : '' }}"
            role="button" data-key="t-signin"> Meus PEPs </a>
        </li>
      @endcan


      </ul>
    </div>
    <!-- Sidebar -->
  </div>

  <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
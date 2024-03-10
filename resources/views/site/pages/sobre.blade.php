@extends('site.layouts.layout-site')
@section('title')
  Sobre
@endsection
@section('content')
  {{-- carousel --}}
  <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="{{ asset('build\images\site\banner-topo-sobre-01.png') }}" class="card-img" alt="...">
      </div>
      <div class="carousel-item">
        <img src="{{ asset('build\images\site\banner-topo-sobre-02.png') }}" class="card-img" alt="...">
      </div>

    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
  {{-- carousel --}}

  <div class="container my-5">
    <div class="row justify-content-center align-items-center text-center">
      <h3 class="bold">A Rede Metrológica RS, pioneira entre as demais Redes estaduais existentes no país, foi
        criada por iniciativa do sistema FIERGS e da Comunidade Científica e Tecnológica do Estado do Rio Grande do
        Sul.
        A Rede é uma associação técnica de cunho técnico-científico, sem fins lucrativos. Atua como articuladora na
        prestação de serviços qualificados de metrologia para o aprimoramento tecnológico das empresas. Desde 1992
        articula parcerias indispensáveis para viabilizar a execução de suas metas.</h3>

    </div>
  </div>

  {{-- cards --}}

  <div class="container my-5  ">
    <div class="row d-flex justify-content-center  align-items-start my-3">
      <div class="col-12 col-md-6  text-center">
        <img src="{{ asset('build\images\site\icon21.png') }}" class="card-img SiteMaxW35 mb-3 " alt="...">
        <h4>COLABORAÇÃO</h4>
        <p>Desde o início, o foco da nossa associação foi voltado para as indústrias, empresas prestadoras de
          serviços, micro e pequenas empresas, entidades públicas e privadas que tenham alguma demanda na área
          da metrologia e qualidade.</p>
      </div>
      <div class="col-12 col-md-6  text-center ">
        <img src="{{ asset('build\images\site\icon24.png') }}" class="card-img SiteMaxW35 mb-3 " alt="...">
        <h4>RECONHECIMENTO</h4>
        <p>Ao se associar à Rede Metrológica RS um laboratório se compromete a abrir suas portas para avaliações
          do seu sistema da qualidade com o objetivo de obter o status de laboratório reconhecido com base na
          ABNT NBR ISO/IEC 17025.</p>
      </div>
      <div class="col-12 col-md-6  text-center ">
        <img src="{{ asset('build\images\site\icon23.png') }}" class="card-img SiteMaxW35 mb-3 " alt="...">
        <h4>QUALIDADE</h4>
        <p>

          Desde 1997 desenvolve um trabalho extremamente sério e responsável. A Rede conquistou a confiança
          dos setores público e empresarial, sendo consolidada como referência para a qualidade, o que pode
          ser atestado pelo grande número de clientes e parceiros, além da participação de instituições de 9
          países em nossos projetos (Alemanha, Canadá, Estados Unidos, Inglaterra, Argentina, Portugal,
          Uruguai, México e Paraguai).</p>
      </div>
      <div class="col-12 col-md-6  text-center ">
        <img src="{{ asset('build\images\site\icon22.png') }}" class="card-img SiteMaxW35 mb-3 " alt="...">
        <h4>CERTIFICAÇÃO E ACREDITAÇÃO</h4>
        <p>Com intuito de garantir a confiabilidade dos serviços ofertados ao mercado, desde 1997 contamos a
          certificação ABNT NBR ISO9001 em todos os nossos processos. Em 2011, ingressamos na acreditação de
          provedor de Ensaios de Proficiência e hoje somos um dos maiores provedores acreditados pela Cgcre do
          Inmetro na norma ABNT NBR ISO/IEC17043.</p>
      </div>
    </div>

    <div class="container my-5  ">
      <div class="row d-flex justify-content-center  align-items-start my-3">
        <div class="col-12 col-md-6  text-center ">

          <h2>Nossa visão </h2>
          <p>Ser reconhecida pela sociedade como fornecedora, com nível de excelência, de soluções em
            metrologia para a qualidade com sustentabilidade.</p>
        </div>
        <div class="col-12 col-md-6  text-center ">

          <h2>Nosso propósito</h2>
          <p>Desde 1992 oferecemos apoio técnico a empresários e a órgãos públicos, com ações sempre pautadas pelo
            “rigorismo metrológico” quanto ao atendimento das normas.</p>
        </div>
        <div class="col-12 col-md-6  text-center">

          <h2>Nossa missão</h2>
          <p>Qualificar e fomentar uma rede de laboratórios, disponibilizar serviços em conformidade com critérios
            internacionais e difundir a cultura metrológica, promovendo parcerias, garantindo nossa
            autossuficiência e contribuindo com o desenvolvimento socioeconômico e tecnológico do País.</p>
        </div>
        <div class="col-12 col-md-6  text-center ">

          <h2>Nossa história</h2>
          <p>A Rede Metrológica RS deu o primeiro passo para sua formação em março de 1990. A partir de então, a
            FIERGS proveu meios para a continuidade dos trabalhos. Após um período de amadurecimento e de
            cuidadosa elaboração do projeto de concepção da Associação Rede de Metrologia e Ensaios do RS, em
            novembro de 1992 realizou-se o lançamento oficial da nossa Associação.</p>
        </div>
      </div>
      <div class="container my-5  ">
        <div class="row d-flex justify-content-center  align-items-start my-3">
          <div class="col-12 col-md-6  text-center">
            <img src="{{ asset('build\images\site\icon21.png') }}" class="card-img SiteMaxW35 mb-3 "
              alt="...">
            <h4>CONSELHO DELIBERATIVO</h4>
          </div>
          <div class="col-12 col-md-6  text-center ">
            <img src="{{ asset('build\images\site\icon22.png') }}" class="card-img SiteMaxW35 mb-3 "
              alt="...">
            <h4>SECRETARIA EXECUTIVA</h4>
          </div>

          <div class="col-12 col-md-6  text-center ">
            <img src="{{ asset('build\images\site\icon23.png') }}" class="card-img SiteMaxW35 mb-3 "
              alt="...">
            <h4>ESTATUTO</h4>
          </div>
          <div class="col-12 col-md-6  text-center ">
            <img src="{{ asset('build\images\site\icon24.png') }}" class="card-img SiteMaxW35 mb-3 "
              alt="...">
            <h4>ATA DE ELEIÇÃO</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- cards --}}

  {{-- banner --}}
  <div class="SiteCards__bgimage p-5 text-center mb-5 text-white "
    style="background-size: cover; background-image: url('{{ asset('build/images/site/banner-associe-se2.png') }}');height:100%; width:100%;">
    <div class="container">
      <h1 class="text-white">SEJA UM ASSOCIADO</h1>
      <p>Faça parte da Rede Metrológica RS.
        Usufrua dos serviços oferecidos nas áreas de avaliações de laboratórios, treinamentos em metrologia,
        programas de ensaios de proficiência e produção dos materiais de referência. Conte com selecionado grupo de
        cientistas, doutores, mestres, metrologistas, instrutores, professores, administradores e avaliadores de
        laboratórios.</p>
      <div class="my-2">
        <a href="/fale-conosco" type="button" class="btn btn-warning btn-lg">Associe-se</a>
      </div>
    </div>
  </div>

  {{-- banner --}}
@endsection

@extends('site.layouts.layout-site')
@section('title') Interlaboratoriais @endsection
@section('content')
  {{-- banner inicial --}}
  <div class="card text-bg-dark">
    <img src="{{ asset('build\images\site\PEP-BANNER-DE-TOPO-1920-x-575-px_B.png') }}" class="card-img" alt="...">

    <div class="card-img-overlay d-flex justify-content-center">
      <div class="align-self-center text-center ">
        <p class="SiteBanner--interlab"><strong>INTERLABORATORIAIS</strong></p>
      </div>
    </div>

  </div>
  {{-- banner inicial --}}

  {{-- baixar --}}
  <div class="container my-5">
    <div class="row  d-flex align-items-center justify-content-center text-center">
      <div class="col">
        <a href="javaScript:void(0);" class="link-secondary link-offset-2 text-decoration-underline">
          <h4>CLIQUE AQUI PARA BAIXAR A PROGRAMAÇÃO ANUAL 
          <i class="ri-file-download-line"></i>
          </h4>
        </a>
      </div>
    </div>
  </div>
  {{-- baixar --}}

  {{-- interlab --}}
  <div class="container">
    <div class="row gy-5">
      @foreach ($interlabs as $agendaInterlab)
      <div class="col-3">
          <div class="card ribbon-box border shadow-none mb-lg-0 card-interlab" style="width: 18rem;">
            @if( $agendaInterlab->inscricao == 1 )
              <div class="ribbon ribbon-primary round-shape">Inscrições abertas</div>
            @endif
            <img src="{{ url( asset('build/images/site/').'/'.$agendaInterlab->interlab->thumb ) }}" 
              class="card-img-top align-self-center pt-2" alt="" style="max-width: 170px" >
            <div class="card-body text-white" style="background-color: #405D71; margin-top: -15px">
              <a href="{{ route('site-single-interlaboratorial', $agendaInterlab->uid)}}" class="text-white bold">
                <h5 class="card-title pb-2">{{ $agendaInterlab->interlab->nome }}</h5>
              </a>

              <a href="{{ route('site-single-interlaboratorial', $agendaInterlab->uid)}}" class="text-start text-white bold">Visualizar <i
                class="fa-solid fa-circle-chevron-right"></i></a>
            </div>
            <div class="card-footer py-2 border-0 text-white" style="background-color: #002C41">
              <i class="bi bi-calendar2-event"></i> &nbsp; {{ \Carbon\Carbon::parse($agendaInterlab->data_inicio)->format('d/m/Y') }}
            </div>
          </div>
        </div>
        @endforeach
    </div>
  </div>
  {{-- interlab --}}


  <!-- helper modal -->
  <div class="modal fade" id="interlabHelper" tabindex="-1" aria-labelledby="interlabHelperLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-right">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="interlabHelperLabel">Estamos atualizando nosso sistema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Estamos em processo de atualização de nosso sistema. <br>
                  A agenda de interlaboratoriais está sendo atualizado e em breve estará completamente disponível. <br><br>
                  Caso o interlaboratorial que você procura não esteja listado, solicite informações através do e-mail:
                  <a href="mailto:interlab@redemetrologica.com.br">interlab@redemetrologica.com.br</a>
                </p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="localStorage.setItem('interlabHelper', 'false')">Não mostrar novamente</button>
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (localStorage.getItem('interlabHelper') !== 'false') { new bootstrap.Modal('#interlabHelper').show(); }
    });
  </script>
  
@endsection

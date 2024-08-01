@if ($errors->has('agenda_interlab_id') || $errors->has('parametro_id'))
  <div class="alert alert-warning">
    <strong>Erro ao salvar os dados!</strong> <br><br>
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h4 class="card-title mb-0">Parametros</h4>
    <a data-bs-toggle="modal" data-bs-target="#modal_parametro_cadastro" class="btn btn-sm btn-success"> <i
        class="ri-add-line align-bottom me-1"></i> Adicionar Parametro </a>
  </div><!-- end card header -->
  <div class="card-body">

    <ul class="list-group list-group-flush">
      @forelse ($interlabParametros as $parametro)
        {{-- <x-painel.avaliadores.modal-dados-bancarios :avaliador="$avaliador" :parametro="$parametro" /> --}}
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <strong>{{ $parametro->parametro->descricao }}</strong>
          </div>
          <div>
            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem" data-bs-toggle="tooltip"
                title="Detalhes e edição"></i>
            </a>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
              <li>
                <x-painel.form-delete.delete route='delete-parametro' id="{{ $parametro->id }}" />
              </li>
            </ul>
          </div>
        </div>
      @empty
        <p>Não há parametro cadastrada</p>
    </ul>
    @endforelse
  </div>
</div>
<x-painel.agenda-interlab.modal-parametros :parametros="$parametros" :agendainterlab="$agendainterlab" />
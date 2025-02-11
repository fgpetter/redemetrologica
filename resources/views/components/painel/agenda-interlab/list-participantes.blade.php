@if ($errors->any())
  <div class="alert alert-warning">
    <strong>Erro ao salvar os dados!</strong> <br><br>
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif


<div class="table-responsive" style="min-height: 180px">
  <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
    @forelse ($interlabempresasinscritas as $empresa)

      <thead class="bg-light">
        <tr>
          <th scope="col" colspan="5"><strong>Empresa: </strong> &nbsp; {{ $empresa->empresa->nome_razao }} - CNPJ: {{ $empresa->empresa->cpf_cnpj }}</th>
        </tr>
      </thead>
        @foreach($intelabinscritos->where('empresa_id', $empresa->empresa_id) as $participante)
          <tr>
            <td style="width: 5%; white-space: nowrap;">
            <a data-bs-toggle="collapse" href="{{"#collapse".$participante->uid}}" role="button" aria-expanded="false" aria-controls="collapseExample">
              <i class="ri-file-text-line btn-ghost ps-2 pe-3 fs-5"></i>
            </a>{{ Carbon\Carbon::parse($participante->data_inscricao)->format('d/m/Y') }}</td>
            <td class="px-3"><b>Laboratório: </b>{{ $participante->laboratorio->nome }}</td>
            <td style="width: 5%; white-space: nowrap;" class="px-3"><b>Valor: </b>{{ $participante->valor ?? '-' }}</td>
            <td style="width: 1%; white-space: nowrap;">
              <div class="dropdown">
                <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                  <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="{{ '#participanteModal' . $participante->uid }}">Editar</a>
                  </li>
                  <li>
                    <x-painel.form-delete.delete route='cancela-inscricao-interlab' id="{{ $participante->uid }}" />
                  </li>
                </ul>
              </div>
            </td>

          </tr>
          <tr>
            <td colspan="5" class="p-0">
              <div class="collapse" id="{{"collapse".$participante->uid}}">
                <div class="row m-3 pe-2">
                  <div class="col-6 text-wrap">
                    <b>Inscrito por:</b> {{ $participante->pessoa->nome_razao }} <br>
                    <b>Informacoes:</b> {{ $participante->informacoes_inscricao }}
                  </div>
                  <div class="col-6 text-wrap">
                    <b>Responsável técnico:</b> {{ $participante->laboratorio->responsavel_tecnico }} <br>
                    <b>Telefone:</b> {{ $participante->laboratorio->telefone }} <b>Email:</b> {{ $participante->laboratorio->email }}<br>
                    <b>Endereço:</b> {{ $participante->laboratorio->endereco->endereco }},
                      {{ $participante->laboratorio->endereco->complemento }}, Bairro: {{ $participante->laboratorio->endereco->bairro }} <br>
                      Cidade: {{ $participante->laboratorio->endereco->cidade }} / 
                      {{ $participante->laboratorio->endereco->uf }}, 
                      CEP: {{ $participante->laboratorio->endereco->cep }}
                  </div>
                </div>
              </div>
            </td>
          </tr>
          <x-painel.agenda-interlab.modal-inscritos :participante="$participante" :agendainterlab="$agendainterlab" />
        @endforeach
    @empty
      <tr> <td colspan="6" class="text-center">Este agendamento não possui inscritos.</td> </tr>
    @endforelse
      @if($intelabinscritos->sum('valor') > 0)
        <tfoot>
          <tr>
            <td colspan="2"></td>
            <td><strong>Total do interlab:</strong> {{ $intelabinscritos->sum('valor') }} </td>
            <td></td>
          </tr>
        </tfoot>
      @endif
  </table>

  <x-painel.agenda-interlab.modal-adicionar-inscrito :agendainterlab="$agendainterlab" :empresas="$empresas"/>

</div>

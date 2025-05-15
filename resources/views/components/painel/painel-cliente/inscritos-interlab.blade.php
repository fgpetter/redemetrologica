<div class="col-12 col-xxl-8">
  <div class="card">
    <div class="card-body">
    <h5 class="h5 mb-3">PEPs e Interlaboratoriais inscritos por você:</h5>
      @foreach ($interlabs->groupBy('agendaInterlab.id') as $agendaGroup)
        <div class="card bg-light shadow-none">
          <div class="card-header bg-light">
            <h6 class="card-title pb-1">{{ $agendaGroup->first()->agendaInterlab->interlab->nome }}</h6>
          </div>

          <div class="card-body bg-light-subtle pt-0">
            @foreach ($agendaGroup->groupBy('empresa.id') as $empresaGroup)
                <h6 class="my-3 text-primary-emphasis">Empresa: &nbsp; {{ $empresaGroup->first()->empresa->nome_razao }}</h6>

                @foreach ($empresaGroup->groupBy('laboratorio.id') as $labGroup)
                  <div class="row">
                    <div class="col-6">
                      <p class="ps-1">
                        <strong>Laboratório: &nbsp; </strong>{{ $labGroup->first()->laboratorio->nome }} <br>
                        <strong>Responsável Técnico:</strong> {{ $labGroup->first()->laboratorio->responsavel_tecnico }} <br>
                        <strong>Telefone: </strong> {{ $labGroup->first()->laboratorio->telefone }} <br>
                        <strong>Email: </strong> {{ $labGroup->first()->laboratorio->email }} <br>
                        <strong>Informações de inscrição:</strong> <br>
                        <span class="ps-1" >{!! nl2br($labGroup->first()->informacoes_inscricao) !!}</span>
                      </p>
                    </div>
                    <div class="col-6">
                      <strong>Endereço: &nbsp; </strong>{{ $labGroup->first()->laboratorio->endereco->endereco }} <br>
                      <strong>Complemento: &nbsp; </strong>{{ $labGroup->first()->laboratorio->endereco->complemento }} <br>
                      <strong>Bairro: &nbsp; </strong>{{ $labGroup->first()->laboratorio->endereco->bairro }} <br>
                      <strong>CEP: &nbsp; </strong>{{ $labGroup->first()->laboratorio->endereco->cep }} <br>
                      <strong>Cidade: &nbsp; </strong>{{ $labGroup->first()->laboratorio->endereco->cidade .' / '. $labGroup->first()->laboratorio->endereco->uf }} <br>
                    </div>
                    <hr>
                  </div>
                @endforeach

            @endforeach
          </div>

          <div class="card-footer bg-light">
            @if($agendaGroup->first()->agendaInterlab->materiais->count() > 0)
            <!-- Primary Alert -->
            <div class="alert alert-primary alert-border-left" role="alert">
              <div class="text-dark">
                <i class="ri-attachment-line me-3 align-middle "></i>
                <strong class="fs-5">Materiais de apoio para o interlab:</strong>
                  <ul class="list-unstyled ms-3 mt-2">
                    @foreach($agendaGroup->first()->agendaInterlab->materiais as $material)
                      <li class="mb-1">
                        <i class="bx bx-file me-1"></i>
                        <a href="{{ asset('storage/' . $material->arquivo) }}" target="_blank" class="text-primary">
                          {{ $material->descricao ?: 'Material ' . $loop->iteration }}
                        </a>
                      </li>
                    @endforeach
                  </ul>
  
              </div>
            </div>
            @endif
            Para acessar mais informações sobre o interlab,
            <a href="{{ route('site-single-interlaboratorial', $agendaGroup->first()->agendaInterlab->uid) }}" class="link-primary"> clique aqui</a>
            <br>
            Caso queira voltar a tela de inscrições,
            <a href="{{ route('interlab-inscricao', ['target' => $agendaGroup->first()->agendaInterlab->uid]) }}" class="link-primary"> clique aqui</a>
          </div>
        </div>
      @endforeach

    </div>
  </div>
</div>

<div>
  @if ($inscricao_interlab && !session()->has('interlab') )
  <div class="col-12 col-xxl-6 col-xl-8">
    <div class="card">
      <div class="card-body">
      <h5 class="h5 mb-3">PEPs e Interlaboratoriais inscrito:</h5>

        <p>
          <strong>Nome:</strong> {{ $inscricao_interlab->agendaInterlab->interlab->nome }} <br>
          <strong>Início:</strong> {{ $inscricao_interlab->agendaInterlab->data_inicio }}
        </p>
          <div class="card bg-light shadow-none">
            <h6 class="card-title mb-0 px-3 pt-2 pb-0">Laboratórios inscritos:</h6>
            <div class="card-body pt-2">
              @foreach ($lab_inscritos as $lab_inscrito)
                @if($lab_inscrito->empresa_id == $inscricao_interlab->empresa_id)
                  <div class="{{ ($loop->index > 0) ? "border-top border-dark pt-3" : ""}}" >
                    <span class="fs-5">{{ $lab_inscrito->laboratorio->nome }}</span> <br>
                    <strong>Responsável Técnico</strong> {{ $lab_inscrito->laboratorio->responsavel_tecnico }} <br>
                    <strong>Telefone</strong> {{ $lab_inscrito->laboratorio->telefone }} <br>
                    <strong>Email</strong> {{ $lab_inscrito->laboratorio->email }} <br>
                    <strong>Informações de inscrição:</strong>
                    <p class="ps-3" >{!! nl2br($lab_inscrito->informacoes_inscricao) !!}</p>
                  </div>
                @endif
              @endforeach
            </div>
          </div>
        <p>Para acessar mais informações sobre o interlab,
          <a href="{{ route('site-single-interlaboratorial', $inscricao_interlab->agendaInterlab->uid) }}" class="link-primary"> clique aqui</a>.
        </p>
        <p>Caso queira voltar a tela de inscrições,
          <a href="{{ route('interlab-inscricao', ['target' => $inscricao_interlab->agendaInterlab->uid]) }}" class="link-primary"> clique aqui</a>.
        </p>

      </div>
    </div>
  </div>
  @endif
</div>
@if (session('agendamento-error'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('agendamento-error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<form method="POST" action="{{ isset($agendacurso->id) ? route('agendamento-curso-update', $agendacurso->uid) : route('agendamento-curso-create') }}">
  @csrf
  <div class="row gy-3">

    <div class="col-sm-4">
      <x-forms.input-select name="status" label="Status">
        <option @selected($agendacurso->status == 'AGENDADO') value="AGENDADO">AGENDADO</option>
        <option @selected($agendacurso->status == 'CANCELADO') value="CANCELADO">CANCELADO</option>
        <option @selected($agendacurso->status == 'CONFIRMADO') value="CONFIRMADO">CONFIRMADO</option>
        <option @selected($agendacurso->status == 'REALIZADO') value="REALIZADO">REALIZADO</option>
        <option @selected($agendacurso->status == 'PROPOSTA  ENVIADA') value="PROPOSTA ENVIADA">PROPOSTA ENVIADA</option>
        <option @selected($agendacurso->status == 'REAGENDAR') value="REAGENDAR">REAGENDAR</option>
        </x-forms.input-select>
      @error('status') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-4">
      <div class="form-check bg-light rounded mt-4 check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
        <input class="form-check-input" name="destaque" value="1" id="destaque" type="checkbox" @checked($agendacurso->destaque) >
        <label class="form-check-label" for="destaque">DESTAQUE</label>
      </div>
      @error('destaque')<div class="text-warning">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-4">
      <x-forms.input-select name="tipo_agendamento" id="tipo_agendamento" label="Tipo de Agendamento">
      <option @selected($agendacurso->tipo_agendamento == 'ONLINE') value="ONLINE">ONLINE</option>
      <option @selected($agendacurso->tipo_agendamento == 'EVENTO') value="EVENTO">EVENTO</option>
      <option @selected($agendacurso->tipo_agendamento == 'IN-COMPANY') value="IN-COMPANY">IN-COMPANY</option>
      </x-forms.input-select>
      @error('tipo_agendamento') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-12">
      <x-forms.input-select name="curso_id" label="Nome do Curso <span class='text-danger'>*</span>">
        <option value="">Selecione um curso</option>
        @foreach ($cursos as $curso)
          <option @selected($agendacurso->curso_id == $curso->id) value="{{$curso->id}}">{{$curso->descricao}}</option>
        @endforeach
      </x-forms.input-select>
      @error('curso_id') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-12 {{ ($agendacurso->tipo_agendamento == 'IN-COMPANY') ? '' : 'd-none' }} " id="cursos-incompany">
      <div class="card border">
        <div class="card-header pt-2">
          <h6 class="h6 mt-0">Cursos IN-COMPANY</h6>
        </div>
        <div class="card-body">
          <div class="row gy-3">

            <div class="col-12">
              <x-forms.input-select name="pessoa_id" label="Empresa">
                <option value="">Selecione</option>
                @foreach ($empresas as $empresa)
                <option @selected($agendacurso->pessoa_id == $empresa->id) value="{{$empresa->id}}">{{$empresa->nome_razao}}</option>
                @endforeach
              </x-forms.input-select>
              @error('pessoa_id') <div class="text-warning">{{ $message }}</div> @enderror
            </div>


            <div class="col-sm-4">
              <x-forms.input-field :value="old('contato') ?? ($agendacurso->contato ?? null)" 
                name="contato" label="Contato" />
              @error('contato') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-3">
              <x-forms.input-field :value="old('contato_email') ?? ($agendacurso->contato_email ?? null)" 
                name="contato_email" label="E-mail" />
              @error('contato_email') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-3">
              <x-forms.input-field :value="old('contato_telefone') ?? ($agendacurso->contato_telefone ?? null)" 
                name="contato_telefone" label="Telefone" />
              @error('contato_telefone') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-sm-2"></div>
            
            <div class="col-sm-3">
              <x-forms.input-field :value="old('num_participantes') ?? ($agendacurso->num_participantes ?? null)" 
                name="num_participantes" label="Número de participantes" />
              @error('num_participantes') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
  
            <div class="col-sm-3">
              <x-forms.input-field :value="old('validade_proposta') ?? ($agendacurso->validade_proposta ?? null)"
                type="date" name="validade_proposta" label="Validade Proposta" />
              @error('validade_proposta') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-3">
              <x-forms.input-field :value="old('valor_orcamento') ?? ($agendacurso->valor_orcamento ?? null)" 
                name="valor_orcamento" label="Valor do orçamento" class="money" />
              @error('valor_orcamento') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-3">
              <x-forms.input-select name="status_proposta" label="Status da Proposta">
                <option value="">Selecione</option>
                <option @selected($agendacurso->pessoa_id == "PENDENTE") value="PENDENTE">PENDENTE</option>
                <option @selected($agendacurso->pessoa_id == "AGUARDANDO APROVACAO") value="AGUARDANDO APROVACAO">AGUARDANDO APROVAÇÃO</option>
                <option @selected($agendacurso->pessoa_id == "APROVADA") value="APROVADA">APROVADA</option>
                <option @selected($agendacurso->pessoa_id == "REPROVADA") value="REPROVADA">REPROVADA</option>
              </x-forms.input-select>
              @error('status_proposta')<div class="text-warning">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-12">
      <x-forms.input-textarea name="endereco_local" label="Local/Endereço">
      {{ old('endereco_local') ?? ($agendacurso->endereco_local ?? null) }}
      </x-forms.input-textarea>
      @error('endereco_local') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-field :value="old('data_inicio') ?? ($agendacurso->data_inicio ?? null)"
        type="date" name="data_inicio" label="Data Inicio  <span class='text-danger'>*</span>" />
      @error('data_inicio') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-field :value="old('data_fim') ?? ($agendacurso->data_fim ?? null)"
        type="date" name="data_fim" label="Data Fim" />
      @error('data_fim') <div class="text-warning">{{ $message }}</div> @enderror
    </div>            
    
    <div class="col-sm-6">
      <x-forms.input-field :value="old('horario') ?? ($agendacurso->horario ?? null)" 
        name="horario" label="Horário" />
      @error('horario') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-select name="carga_horaria" label="Carga Horária">
        <option @selected($agendacurso->carga_horaria == "0") value="0">0</option>
        <option @selected($agendacurso->carga_horaria == "2") value="2">2</option>
        <option @selected($agendacurso->carga_horaria == "4") value="4">4</option>
        <option @selected($agendacurso->carga_horaria == "6") value="6">6</option>
        <option @selected($agendacurso->carga_horaria == "8") value="8">8</option>
        <option @selected($agendacurso->carga_horaria == "12") value="12">12</option>
        <option @selected($agendacurso->carga_horaria == "16") value="16">16</option>
        <option @selected($agendacurso->carga_horaria == "20") value="20">20</option>
        <option @selected($agendacurso->carga_horaria == "24") value="24">24</option>
        <option @selected($agendacurso->carga_horaria == "32") value="32">32</option>
        <option @selected($agendacurso->carga_horaria == "36") value="36">36</option>
        <option @selected($agendacurso->carga_horaria == "40") value="40">40</option>
      </x-forms.input-select>
      @error('carga_horaria') <div class="text-warning">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-3">
      <div class="form-check bg-light rounded mt-4 check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
        <input class="form-check-input" name="inscricoes" value="1" id="inscricoes" type="checkbox" @checked($agendacurso->inscricoes)>
        <label class="form-check-label" for="inscricoes">INSCRIÇÕES</label>
      </div>
      @error('inscricoes') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-3">
      <div class="form-check bg-light rounded mt-4 check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
        <input class="form-check-input" name="site" value="1" id="site" type="checkbox" @checked($agendacurso->site)>
        <label class="form-check-label" for="site">SITE</label>
      </div>
      @error('site') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-12">
      <x-forms.input-select name="instrutor_id" label="Instrutor <span class='text-danger'>*</span>">
        <option value="">Selecione um instrutor</option>
        @foreach ($instrutores as $instrutor)
          <option @selected($agendacurso->instrutor_id == $instrutor->id) value="{{$instrutor->id}}">{{$instrutor->pessoa->nome_razao}}</option>
        @endforeach
      </x-forms.input-select>
      @error('instrutor_id')<div class="text-warning">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-4">
      <x-forms.input-field :value="old('investimento') ?? ($agendacurso->investimento ?? null)" 
        name="investimento" label="Investimento" class="money"/>
      @error('investimento')<div class="text-warning">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-4">
      <x-forms.input-field :value="old('investimento_associado') ?? ($agendacurso->investimento_associado ?? null)" 
        name="investimento_associado" label="Investimento Associado" class="money"/>
      @error('investimento_associado')<div class="text-warning">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-field :value="old('data_limite_pagamento') ?? ($agendacurso->data_limite_pagamento ?? null)"
        type="date" name="data_limite_pagamento" label="Data Limite Pagamento" />
      @error('data_limite_pagamento') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-12">
      <x-forms.input-textarea name="observacoes" label="Observações">
      {{ old('observacoes') ?? ($agendacurso->obs ?? null) }}
      </x-forms.input-textarea>
      @error('observacoes')<div class="text-warning">{{ $message }}</div>@enderror
    </div>

  </div>

  <div class="row mt-3">
    <div class="col-sm-6">
      <button type="submit" class="btn btn-primary px-4"> 
        {{ isset($agendacurso->id) ? 'ATUALIZAR' : 'CADASTRAR'}}
      </button>      
    </div>
  </div>
</form>
@if($curso->id)
  <x-painel.cursos.form-delete route="agendamento-curso-delete" id="{{$agendacurso->uid}}" />
@endif
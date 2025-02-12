<form method="POST"
  action="{{ isset($agendacurso->id) ? route('agendamento-curso-update', $agendacurso->uid) : route('agendamento-curso-create') }}">
  @csrf
  <div class="row gy-3">

    <div class="col-sm-4">
      <x-forms.input-select name="status" label="Status">
        <option @selected($agendacurso->status == 'AGENDADO') value="AGENDADO">AGENDADO</option>
        <option @selected($agendacurso->status == 'CANCELADO') value="CANCELADO">CANCELADO</option>
        <option @selected($agendacurso->status == 'CONFIRMADO') value="CONFIRMADO">CONFIRMADO</option>
        <option @selected($agendacurso->status == 'REALIZADO') value="REALIZADO">REALIZADO</option>
        <option @selected($agendacurso->status == 'PROPOSTA ENVIADA') value="PROPOSTA ENVIADA">PROPOSTA ENVIADA</option>
        <option @selected($agendacurso->status == 'REAGENDAR') value="REAGENDAR">REAGENDAR</option>
      </x-forms.input-select>
    </div>

    <div class="col-sm-4">
      <x-forms.input-check-pill name='destaque' label='DESTAQUE' :checked="$agendacurso->destaque == 1" />
      @error('destaque')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-4">
      <x-forms.input-select name="tipo_agendamento" id="tipo_agendamento" label="Tipo de Agendamento">
        <option @selected($agendacurso->tipo_agendamento == 'ONLINE') value="ONLINE">ONLINE</option>
        <option @selected($agendacurso->tipo_agendamento == 'EVENTO') value="EVENTO">EVENTO</option>
        <option @selected($agendacurso->tipo_agendamento == 'IN-COMPANY') value="IN-COMPANY">IN-COMPANY</option>
      </x-forms.input-select>
    </div>

    <div class="col-sm-12">
      <x-forms.input-select name="curso_id" label="Nome do Curso <span class='text-danger'>*</span>">
        <option value="">Selecione um curso</option>
        @if ($cursoatual)
          <option selected value="{{ $cursoatual->id }}">{{ $cursoatual->descricao }}</option>
        @endif
        @foreach ($cursos as $curso)
          <option value="{{ $curso->id }}">{{ $curso->descricao }}</option>
        @endforeach
      </x-forms.input-select>
    </div>

    <div class="col-sm-12 {{ $agendacurso->tipo_agendamento == 'IN-COMPANY' ? '' : 'd-none' }} " id="cursos-incompany">
      <div class="card border">
        <div class="card-header pt-2">
          <h6 class="h6 mt-0">Cursos IN-COMPANY</h6>
        </div>
        <div class="card-body">
          <div class="row gy-3">

            <div class="col-12">
              <x-forms.input-select name="empresa_id" label="Empresa">
                <option value="">Selecione</option>
                @foreach ($empresas as $empresa)
                  <option @selected($agendacurso->empresa_id == $empresa->id) value="{{ $empresa->id }}">
                    {{ $empresa->nome_razao }}</option>
                @endforeach
              </x-forms.input-select>
            </div>


            <div class="col-sm-4">
              <x-forms.input-field :value="old('contato') ?? ($agendacurso->contato ?? null)" name="contato" label="Contato" />
              @error('contato')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-sm-3">
              <x-forms.input-field :value="old('contato_email') ?? ($agendacurso->contato_email ?? null)" 
                name="contato_email" label="E-mail" />
              @error('contato_email')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-sm-3">
              <x-forms.input-field :value="old('contato_telefone') ?? ($agendacurso->contato_telefone ?? null)" 
                name="contato_telefone" label="Telefone" />
              @error('contato_telefone')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-sm-2"></div>

            <div class="col-sm-3">
              <x-forms.input-field :value="old('num_participantes') ?? ($agendacurso->num_participantes ?? null)" 
                name="num_participantes" label="Número de participantes" />
              @error('num_participantes')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-sm-3">
              <x-forms.input-field :value="old('validade_proposta') ?? ($agendacurso->validade_proposta ?? null)" 
                type="date" name="validade_proposta" label="Validade Proposta" />
              @error('validade_proposta')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-sm-3">
              <x-forms.input-field :value="old('valor_orcamento') ?? ($agendacurso->valor_orcamento ?? null)" 
                name="valor_orcamento" label="Valor do orçamento" class="money" />
              @error('valor_orcamento')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-sm-3">
              <x-forms.input-select name="status_proposta" label="Status da Proposta">
                <option value="">Selecione</option>
                <option @selected($agendacurso->status_proposta == 'PENDENTE') value="PENDENTE"> PENDENTE </option>
                <option @selected($agendacurso->status_proposta == 'AGUARDANDO APROVACAO') value="AGUARDANDO APROVACAO"> AGUARDANDO APROVAÇÃO </option>
                <option @selected($agendacurso->status_proposta == 'APROVADA') value="APROVADA"> APROVADA </option>
                <option @selected($agendacurso->status_proposta == 'REPROVADA') value="REPROVADA"> REPROVADA </option>
              </x-forms.input-select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-12">
      <x-forms.input-textarea name="endereco_local" label="Local/Endereço">
        {{ old('endereco_local') ?? ($agendacurso->endereco_local ?? null) }}
      </x-forms.input-textarea>
      @error('endereco_local')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-field :value="old('data_inicio') ?? ($agendacurso->data_inicio ?? null)" type="date" name="data_inicio"
        label="Data Inicio  <span class='text-danger'>*</span>" />
      @error('data_inicio')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-field :value="old('data_fim') ?? ($agendacurso->data_fim ?? null)" type="date" name="data_fim" label="Data Fim" />
      @error('data_fim')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-6">
      <x-forms.input-field :value="old('horario') ?? ($agendacurso->horario ?? null)" name="horario" label="Horário" />
      @error('horario')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-select name="carga_horaria" label="Carga Horária">
        <option @selected($agendacurso->carga_horaria == '0') value="0">0</option>
        <option @selected($agendacurso->carga_horaria == '2') value="2">2</option>
        <option @selected($agendacurso->carga_horaria == '4') value="4">4</option>
        <option @selected($agendacurso->carga_horaria == '6') value="6">6</option>
        <option @selected($agendacurso->carga_horaria == '8') value="8">8</option>
        <option @selected($agendacurso->carga_horaria == '12') value="12">12</option>
        <option @selected($agendacurso->carga_horaria == '16') value="16">16</option>
        <option @selected($agendacurso->carga_horaria == '20') value="20">20</option>
        <option @selected($agendacurso->carga_horaria == '24') value="24">24</option>
        <option @selected($agendacurso->carga_horaria == '32') value="32">32</option>
        <option @selected($agendacurso->carga_horaria == '36') value="36">36</option>
        <option @selected($agendacurso->carga_horaria == '40') value="40">40</option>
      </x-forms.input-select>
    </div>

    <div class="col-sm-3" id="input-inscricoes">
      <x-forms.input-check-pill name='inscricoes' label='INSCRIÇÕES' :checked="$agendacurso->inscricoes == 1" />
      @error('inscricoes')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-3" id="input-site">
      <x-forms.input-check-pill name='site' label='SITE' :checked="$agendacurso->site == 1" />
      @error('site')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-12">
      <x-forms.input-select name="instrutor_id" label="Instrutor <span class='text-danger'>*</span>">
        <option value="">Selecione um instrutor</option>
        @if ($instrutoratual)
          <option selected value="{{ $instrutoratual->id }}">{{ $instrutoratual->pessoa->nome_razao }}
          </option>
        @endif
        @foreach ($instrutores as $instrutor)
          <option value="{{ $instrutor->id }}">{{ $instrutor->pessoa->nome_razao }}</option>
        @endforeach
      </x-forms.input-select>
    </div>

    @if($agendacurso->uid)
    <div class="col-12">
      <div class="card border shadow-none">
        <div class="card-header pt-2">
          <h6 class="h6 mt-0">Selecionar materiais disponíveis</h6>
        </div>
        <div class="card-body">
          <div class="row">
            @forelse ($agendacurso->curso->materiais as $material)
              <div class="form-check mb-2">
                <input class="form-check-input" name="material[]" value="{{ $material->id }}" type="checkbox" id="{{ $material->uid }}">
                <label class="form-check-label d-inline-flex align-items-center" for="{{ $material->uid }}">
                  {!! ($material->descricao) ? $material->descricao . '&nbsp; <i class="ph-arrow-right fs-5"></i> &nbsp;' : '' !!}
                  {{ $material->arquivo }}
                </label>&nbsp;
              </div>
            @empty
              <p> O curso {{$agendacurso->curso->descricao}} não tem materiais.</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
    @endif


    <div class="col-sm-4" id="input-investimento">
      <x-forms.input-field :value="old('investimento') ?? ($agendacurso->investimento ?? null)" 
        name="investimento" id="investimento" label="Investimento" class="money" />
      @error('investimento')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-4" id="input-investimento_associado">
      <x-forms.input-field :value="old('investimento_associado') ?? ($agendacurso->investimento_associado ?? null)" 
        name="investimento_associado" id="investimento_associado"
        label="Investimento Associado" class="money" />
      @error('investimento_associado')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-12">
      <x-forms.input-textarea name="observacoes"
        label="Observações">{{ old('observacoes') ?? ($agendacurso->observacoes ?? null) }}
      </x-forms.input-textarea>
      @error('observacoes')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

  </div>

  <div class="row mt-3">
    <div class="col-sm-6">
      <button type="submit" class="btn btn-primary px-4">
        {{ isset($agendacurso->id) ? 'ATUALIZAR' : 'CADASTRAR' }}
      </button>
    </div>
  </div>
</form>

@if ($agendacurso->id)
  <x-painel.form-delete.delete route='agendamento-curso-delete' id="{{ $agendacurso->uid }}" label="Agendamento de curso" />
@endif

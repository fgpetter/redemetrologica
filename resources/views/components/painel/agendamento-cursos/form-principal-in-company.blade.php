@if($errors->any())
  @foreach($errors->all() as $error)
    <div class="alert alert-warning">{{ $error }}</div>
  @endforeach
@endif

<form method="POST"
  action="{{ isset($agendacurso->id) ? route('agendamento-curso-in-company-update', $agendacurso->uid) : route('agendamento-curso-in-company-create') }}">
  @csrf
  <div class="row gy-3">
    
    <input type="hidden" name="tipo_agendamento" value="IN-COMPANY">
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

    <div class="col-12">
      <x-forms.input-select name="empresa_id" label="Empresa" id="empresa">
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

    <div class="col-sm-4">
      <x-forms.input-field :value="old('contato_email') ?? ($agendacurso->contato_email ?? null)" 
        name="contato_email" label="E-mail" />
      @error('contato_email')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-4">
      <x-forms.input-field :value="old('contato_telefone') ?? ($agendacurso->contato_telefone ?? null)" 
        name="contato_telefone" label="Telefone" />
      @error('contato_telefone')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-12 mt-4"></div>

    <div class="col-sm-3">
      <label class="form-label mb-0">Participantes</label>
      <input class="form-control" type="text" value="{{ $agendacurso?->inscritos?->count() }}" readonly>
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
      <x-forms.input-field name="carga_horaria" label="Carga Horária" type="number" 
        :value="old('carga_horaria') ?? ($agendacurso->carga_horaria ?? null) ?? ($agendacurso->curso->carga_horaria ?? null)"/>
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
                <input class="form-check-input" name="material[]" value="{{ $material->id }}" type="checkbox" id="{{ $material->uid }}"
                  @checked( $agendacurso->cursoMateriais->contains( $material ) ) >
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
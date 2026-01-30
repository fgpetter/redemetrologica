<form method="POST" id="form-agendamento-curso"
  action="{{ isset($agendacurso->id) ? route('agendamento-curso-update', $agendacurso->uid) : route('agendamento-curso-create') }}">
  @csrf
  <div class="row gy-3">

    <div class="col-sm-4">
      <x-forms.input-select name="status" id="status-agendamento" label="Status">
        <option @selected($agendacurso->status == 'AGENDADO') value="AGENDADO">AGENDADO</option>
        <option @selected($agendacurso->status == 'CANCELADO') value="CANCELADO">CANCELADO</option>
        <option @selected($agendacurso->status == 'CONFIRMADO') value="CONFIRMADO">CONFIRMADO</option>
        <option @selected($agendacurso->status == 'REALIZADO') value="REALIZADO">REALIZADO</option>
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

    <div class="col-sm-12">
      <x-forms.input-textarea name="endereco_local" label="Local/Endereço">
        {{ old('endereco_local') ?? ($agendacurso->endereco_local ?? null) }}
      </x-forms.input-textarea>
      @error('endereco_local')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-field :value="old('data_inicio', $agendacurso->data_inicio?->format('Y-m-d'))" type="date" name="data_inicio"
        label="Data Inicio  <span class='text-danger'>*</span>" />
      @error('data_inicio')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-field :value="old('data_fim', $agendacurso->data_fim?->format('Y-m-d'))" type="date" name="data_fim" label="Data Fim" />
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
      <x-forms.input-field name="carga_horaria" label="Carga Horária" type="number" :value="old('carga_horaria') ?? ($agendacurso->carga_horaria ?? null)"/>
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

<script>
  document.getElementById('form-agendamento-curso').addEventListener('submit', function(e) {
    const statusSelect = document.getElementById('status-agendamento');
    const oldStatus = "{{ $agendacurso->status }}";
    
    if (statusSelect.value === 'REALIZADO' && oldStatus !== 'REALIZADO') {
      e.preventDefault();
      Swal.fire({
        title: 'Confirmação',
        text: 'Ao salvar esse curso como realizado, todos os certificados serão enviados para os inscritos com pagamento quitado. Deseja continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, continuar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit();
        }
      });
    }
  });
</script>

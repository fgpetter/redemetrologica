<div class="col-8">
  <form method="POST"
    action="{{ isset($agendainterlab->id) ? route('agenda-interlab-update', $agendainterlab->uid) : route('agenda-interlab-create') }}">
    @csrf
    <div class="row">
      <div class="col-12">
        <x-forms.input-select name="interlab_id" label="Interlaboratorial">
          @foreach ($interlabs as $interlab)
            <option value="">Selecione</option>
            <option @selected( $agendainterlab->interlab_id == $interlab->id ) value="{{ $interlab->id }}">{{ $interlab->nome }}</option>
          @endforeach
        </x-forms.input-select>
        @error('interlab_id') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-sm-4">
        <x-forms.input-select name="status" label="Status">
          <option @selected($agendainterlab->status == 'PENDENTE') value="PENDENTE">PENDENTE</option>
          <option @selected($agendainterlab->status == 'APROVADO') value="APROVADO">APROVADO</option>
          <option @selected($agendainterlab->status == 'REPROVADO') value="REPROVADO">REPROVADO</option>
        </x-forms.input-select>
        @error('status') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-sm-4">
        <x-forms.input-select name="tipo" label="Tipo">
          <option @selected($agendainterlab->tipo == 'BILATERAL') value="BILATERAL">BILATERAL</option>
          <option @selected($agendainterlab->tipo == 'INTERLABORATORIAL') value="INTERLABORATORIAL">INTERLABORATORIAL</option>
        </x-forms.input-select>
        @error('tipo') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-sm-3">
        <div class="form-check bg-light rounded mt-4 check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
          <input class="form-check-input" name="inscricao" value="1" id="inscricao" type="checkbox"
            @checked($agendainterlab->inscricao ?? false)>
          <label class="form-check-label" for="inscricao">INSCRIÇÕES</label>
        </div>
        @error('inscricao') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
  
      <div class="col-sm-3">
        <div class="form-check bg-light rounded mt-4 check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
          <input class="form-check-input" name="site" value="1" id="site" type="checkbox"
            @checked($agendainterlab->site ?? false)>
          <label class="form-check-label" for="site">SITE</label>
        </div>
        @error('site') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
  
      <div class="col-sm-3">
        <div class="form-check bg-light rounded mt-4 check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
          <input class="form-check-input" name="destaque" value="1" id="destaque" type="checkbox"
            @checked($agendainterlab->destaque ?? false)>
          <label class="form-check-label" for="destaque">DESTAQUE</label>
        </div>
        @error('destaque') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="row gy-3 mt-1">
      <div class="col-sm-12">
        <x-forms.input-textarea name="descricao" label="Descrição" helper='Se preenchido irá substituir a descrição do interlab selecionado'>
          {{ old('descricao') ?? ($agendainterlab->descricao ?? null) }}
        </x-forms.input-textarea>
        @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
  
      <div class="col-sm-3">
        <x-forms.input-field :value="old('data_inicio') ?? ($agendainterlab->data_inicio?->format('Y-m-d') ?? null)" type="date" name="data_inicio"
          label="Data Inicio"/>
        @error('data_inicio') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
  
      <div class="col-sm-3">
        <x-forms.input-field :value="old('data_fim') ?? ($agendainterlab->data_fim?->format('Y-m-d') ?? null)" type="date" name="data_fim"
          label="Data Final"/>
        @error('data_fim') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
  
      <div class="col-sm-2">
        <x-forms.input-select name="sob_demanda" label="Sob Demanda">
          <option value="0">NÃO</option>
          <option @selected($agendainterlab->sob_demanda ?? false) value="1">SIM</option>
        </x-forms.input-select>
        @error('sob_demanda') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
    </div>
  
    <div class="row mt-3">
      <div class="col-sm-6">
        <button type="submit" class="btn btn-primary px-4">
          {{ isset($agendainterlab->id) ? 'ATUALIZAR' : 'CADASTRAR' }}
        </button>
      </div>
    </div>
  
  </form>
  
  @if ($agendainterlab->id ?? false)
    <x-painel.form-delete.delete route='agendamento-curso-delete' id="{{ $agendainterlab->uid }}"
      label="Agendamento de curso" />
  @endif
</div>


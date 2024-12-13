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
    <div class="col-sm-3">
      <x-forms.input-select name="status" label="Status">
        <option @selected($agendainterlab->status == 'AGENDADO') value="AGENDADO">AGENDADO</option>
        <option @selected($agendainterlab->status == 'CONFIRMADO') value="CONFIRMADO">CONFIRMADO</option>
        <option @selected($agendainterlab->status == 'CONCLUIDO') value="CONCLUIDO">CONCLUIDO</option>
      </x-forms.input-select>
      @error('status') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-2">
      <x-forms.input-field :value="old('data_inicio') ?? ($agendainterlab->data_inicio?->format('Y-m-d') ?? null)" type="date" name="data_inicio"
        label="Data Inicio"/>
      @error('data_inicio') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-2">
      <x-forms.input-field :value="old('data_fim') ?? ($agendainterlab->data_fim?->format('Y-m-d') ?? null)" type="date" name="data_fim"
        label="Data Final"/>
      @error('data_fim') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-3">
      <x-forms.input-select name="certificado" label="Emitir certificado por:">
        <option @selected($agendainterlab->certificado == 'EMPRESA') value="EMPRESA">EMPRESA</option>
        <option @selected($agendainterlab->certificado == 'PARTICIPANTE') value="PARTICIPANTE">PARTICIPANTE</option>
      </x-forms.input-select>
      @error('certificado') <div class="text-warning">{{ $message }}</div> @enderror
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

    <div class="col-12 col-sm-9 mt-4">
      <div class="card border rouded shadow-none">
        <div class="card-body">
          <h6 class="card-subtitle mb-2">Valores de inscrição por rodada conforme região</h6>
          <div class="row">

            <div class="col col-sm-3">
              <x-forms.input-field :value="old('valor_rs') ?? ($agendainterlab->valor_rs ?? null)" type="text" name="valor_rs"
                label="Valor base - RS" class="money"/>
              @error('valor_rs') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col col-sm-3">
              <x-forms.input-field :value="old('valor_s_se') ?? ($agendainterlab->valor_s_se ?? null)" type="text" name="valor_s_se"
                label="Sul e Sudeste" class="money"/>
              @error('valor_s_se') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col col-sm-3">
              <x-forms.input-field :value="old('valor_co') ?? ($agendainterlab->valor_co ?? null)" type="text" name="valor_co"
                label="Centro Oeste" class="money"/>
              @error('valor_co') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col col-sm-3">
              <x-forms.input-field :value="old('valor_n_ne') ?? ($agendainterlab->valor_n_ne ?? null)" type="text" name="valor_n_ne"
                label="Norte e Nordeste" class="money"/>
              @error('valor_n_ne') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="row gy-3 mt-1">
    <div class="col-12">
      <h6 class="card-subtitle mb-2">Descrição do Programa</h6>
      <textarea id="editor" class="ckeditor-classic" name="descricao">{!! old('descricao') ?? ($agendainterlab->descricao ?? null) !!}</textarea>
      @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
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

<script src="{{ URL::asset('build/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>
<script>
  var ckClassicEditor = document.querySelectorAll(".ckeditor-classic")
  if (ckClassicEditor) {
    Array.from(ckClassicEditor).forEach(function() {
      ClassicEditor
        .create(document.querySelector('.ckeditor-classic'), {
          ckfinder: {
            uploadUrl: '{{ route('image-upload') . '?_token=' . csrf_token() }}',
          }
        })
        .then(function(editor) {
          editor.ui.view.editable.element.style.height = '45vh';
        })
        .catch(function(error) {
          console.error(error);
        });
    });
  }
</script>
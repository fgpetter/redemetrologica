<form method="POST"
  action="{{ isset($agendainterlab->id) ? route('agenda-interlab-update', $agendainterlab->uid) : route('agenda-interlab-create') }}">
  @csrf
  <div class="row">
    <div class="col-12">
      <x-forms.input-select name="interlab_id" label="Interlaboratorial" errorBag="principal" required>
        <option value="">Selecione</option>
        @foreach ($interlabs as $interlab)
          <option @selected($agendainterlab->interlab_id == $interlab->id) value="{{ $interlab->id }}">
            {{ $interlab->nome }}</option>
        @endforeach
      </x-forms.input-select>
      @error('interlab_id', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-3 col-xxl-3">
      <x-forms.input-select name="status" label="Status" errorBag="principal">
        <option @selected($agendainterlab->status == 'AGENDADO') value="AGENDADO">AGENDADO</option>
        <option @selected($agendainterlab->status == 'CONFIRMADO') value="CONFIRMADO">CONFIRMADO</option>
        <option @selected($agendainterlab->status == 'CONCLUIDO') value="CONCLUIDO">CONCLUIDO</option>
      </x-forms.input-select>
      @error('status', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 col-xxl-3">
      <x-forms.input-select name="ano_referencia" label="Ano Referência" id="input_ano_referencia">
        <option value="">Selecione</option>
        @for ($i = date('Y') - 1; $i <= date('Y') + 2; $i++)
          <option @selected($agendainterlab->ano_referencia == $i) value="{{ $i }}">{{ $i }}</option>
        @endfor
      </x-forms.input-select>
      @error('ano_referencia', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 col-xxl-3">
      <x-forms.input-field name="tag_senha" label="TAG (código)"
        tooltip="TAG gerada automaticamente com base no código do interlaboratorial."
        class="text-uppercase form-control-plaintext" :value="$agendainterlab->interlab->tag ?? null"
        placeholder="Não Preenchido" readonly />
      @error('tag_senha', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
  </div>

  {{-- Sessão de datas --}}
  <div class="row mt-1 gy-3">
    <div class="col-md-4 col-xxl-3">
      <x-forms.input-field :value="old('data_inicio') ?? ($agendainterlab->data_inicio?->format('Y-m-d') ?? null)"
        type="date" name="data_inicio" label="Data Inicio" required />
      @error('data_inicio', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 col-xxl-3">
      <x-forms.input-field :value="old('data_fim') ?? ($agendainterlab->data_fim?->format('Y-m-d') ?? null)" type="date"
        name="data_fim" label="Data Final" />
      @error('data_fim', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 col-xxl-3">
      <x-forms.input-field :value="old('data_limite_inscricao') ?? ($agendainterlab->data_limite_inscricao?->format('Y-m-d') ?? null)" type="date" name="data_limite_inscricao"
        label="Limite inscrição" required />
      @error('data_limite_inscricao', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 col-xxl-3" >
      <x-forms.input-field :value="old('valor_desconto') ?? ($agendacurso->valor_desconto ?? null)" 
        tooltip="Valor de desconto para clientes que contratam todas rodadas."
        name="valor_desconto" id="valor_desconto"
        label="Desconto" class="money" />
      @error('valor_desconto')
        <div class="text-warning">{{ $message }}</div>
      @enderror
    </div>

  </div>

  <hr class="my-4">
  <div class="row">
    <div class="col-6 col-lg-3">
      @if($agendainterlab->interlab->tag)
        <div class="form-check bg-light rounded check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
          <input class="form-check-input" name="inscricao" value="1" id="inscricao" type="checkbox"
            @checked($agendainterlab->inscricao ?? false)>
          <label class="form-check-label" for="inscricao">INSCRIÇÕES</label>
        </div>
      @else
        <div class="form-check bg-light rounded check-bg" style="padding: 0.8rem 1.8rem 0.8rem;"
          data-bs-toggle="tooltip" data-bs-html="true" title="O interlaboratorial não possui tag.">
          <input class="form-check-input" name="inscricao" value="1" id="inscricao" type="checkbox" disabled>
          <label class="form-check-label" for="inscricao">INSCRIÇÕES</label>
        </div>
      @endif
      @error('inscricao', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-6 col-md-4 col-xxl-3">
      <div class="form-check bg-light rounded check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
        <input class="form-check-input" name="site" value="1" id="site" type="checkbox" @checked($agendainterlab->site ?? false)>
        <label class="form-check-label" for="site">SITE</label>
      </div>
      @error('site', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-6 col-md-4 col-xxl-3">
      <div class="form-check bg-light rounded check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
        <input class="form-check-input" name="destaque" value="1" id="destaque" type="checkbox"
          @checked($agendainterlab->destaque ?? false)>
        <label class="form-check-label" for="destaque">DESTAQUE</label>
      </div>
      @error('destaque', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
      <small class="text-muted">Somente interlaboratoriais com tag podem receber inscrições.</small>
    </div>

    <div class="col-12 mt-4">
      <div class="card border rouded shadow-none">
        <div class="card-body">

          <x-forms.input-textarea name="instrucoes_inscricao" label="Instruções ao cliente:"
            tooltip="Informações que o cliente irá visualizar na tela de inscrição">{{ $agendainterlab->instrucoes_inscricao ?? null }}
          </x-forms.input-textarea>

        </div>
      </div>
    </div>
  </div>

  <div class="row gy-3 mt-1">
    <div class="col-12">
      <h6 class="card-subtitle mb-2">Descrição do Programa</h6>
      <textarea id="editor" class="ckeditor-classic"
        name="descricao">{!! old('descricao') ?? ($agendainterlab->descricao ?? null) !!}</textarea>
      @error('descricao', 'principal') <div class="text-warning">{{ $message }}</div> @enderror
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
  const ckClassicEditor = document.querySelectorAll(".ckeditor-classic");
  if (ckClassicEditor) {
    Array.from(ckClassicEditor).forEach(() => {
      ClassicEditor
        .create(document.querySelector('.ckeditor-classic'), {
          ckfinder: {
            uploadUrl: '{{ route('image-upload') . '?_token=' . csrf_token() }}',
          }
        })
        .then(function (editor) {
          editor.ui.view.editable.element.style.height = '45vh';
        })
        .catch(function (error) {
          console.error(error);
        });
    });
  }
</script>
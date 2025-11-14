<form method="POST"
  action="{{ isset($agendainterlab->id) ? route('agenda-interlab-update', $agendainterlab->uid) : route('agenda-interlab-create') }}">
  @csrf
  <div class="row">
    <div class="col-12">
      <x-forms.input-select name="interlab_id" label="Interlaboratorial" errorBag="principal" required>
        <option value="">Selecione</option>
        @foreach ($interlabs as $interlab)
          <option @selected( $agendainterlab->interlab_id == $interlab->id ) value="{{ $interlab->id }}">{{ $interlab->nome }}</option>
        @endforeach
      </x-forms.input-select>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-4 col-xxl-3">
      <x-forms.input-select name="status" label="Status" errorBag="principal">
        <option @selected($agendainterlab->status == 'AGENDADO') value="AGENDADO">AGENDADO</option>
        <option @selected($agendainterlab->status == 'CONFIRMADO') value="CONFIRMADO">CONFIRMADO</option>
        <option @selected($agendainterlab->status == 'CONCLUIDO') value="CONCLUIDO">CONCLUIDO</option>
      </x-forms.input-select>
    </div>

    

    <div class="col-md-4 col-xxl-3">
      <x-forms.input-select name="certificado" label="Emitir certificado por:" errorBag="principal">
        <option @selected($agendainterlab->certificado == 'EMPRESA') value="EMPRESA">EMPRESA</option>
        <option @selected($agendainterlab->certificado == 'PARTICIPANTE') value="PARTICIPANTE">PARTICIPANTE</option>
      </x-forms.input-select>
    </div>

    <div class="col-md-4 col-xxl-3">
      <x-forms.input-select name="ano_referencia" label="Ano Referência" id="input_ano_referencia" required>
        <option value="">Selecione</option>
        @for ($i = date('Y') - 1; $i <= date('Y') + 2; $i++)
          <option @selected($agendainterlab->ano_referencia == $i) value="{{ $i }}">{{ $i }}</option>
        @endfor
      </x-forms.input-select>
    </div>

  </div>

  {{-- Sessão de datas --}}
  <hr class="mt-4">
  <div class="row gy-3"> 
    <div class="col-md-4 col-xxl-3">
      <x-forms.input-field :value="old('data_inicio') ?? ($agendainterlab->data_inicio?->format('Y-m-d') ?? null)" type="date" name="data_inicio"
        label="Data Inicio" required/>
      @error('data_inicio','principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 col-xxl-3">
      <x-forms.input-field :value="old('data_fim') ?? ($agendainterlab->data_fim?->format('Y-m-d') ?? null)" type="date" name="data_fim"
        label="Data Final" />
      @error('data_fim','principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 col-xxl-3">
      <x-forms.input-field 
        :value="old('data_limite_inscricao') ?? ($agendainterlab->data_limite_inscricao?->format('Y-m-d') ?? null)" 
        type="date" name="data_limite_inscricao" label="Limite inscrição" />
    </div>
    <div class="col-md-4 col-xxl-3">
      <x-forms.input-field 
        :value="old('data_limite_envio_ensaios') ?? ($agendainterlab->data_limite_envio_ensaios?->format('Y-m-d') ?? null)" 
        type="date" name="data_limite_envio_ensaios" label="Limite Envio ensaios" />
    </div>

    <div class="col-md-4 col-xxl-3">
      <x-forms.input-field 
        :value="old('data_inicio_ensaios') ?? ($agendainterlab->data_inicio_ensaios?->format('Y-m-d') ?? null)" 
        type="date" name="data_inicio_ensaios" label="Inicio ensaios" />
    </div>
    <div class="col-md-4 col-xxl-3">
      <x-forms.input-field 
        :value="old('data_limite_envio_resultados') ?? ($agendainterlab->data_limite_envio_resultados?->format('Y-m-d') ?? null)" 
        type="date" name="data_limite_envio_resultados" label="Envio resultados" />
    </div>
    <div class="col-md-4 col-xxl-3" >
      <x-forms.input-field 
        :value="old('data_divulgacao_relatorios') ?? ($agendainterlab->data_divulgacao_relatorios?->format('Y-m-d') ?? null)" 
        type="date" name="data_divulgacao_relatorios" label="Divulgação relatórios" />
    </div>
  </div>
  <hr class="mt-4">

  <div class="row mt-3">
    <div class="col-12">
      <div class="card border rouded shadow-none">
        <div class="card-body">
          <h6 class="card-subtitle mb-2 text-primary-emphasis">Valores rodada:</h6>
          
          <div id="valores-wrapper">
            @if(isset($agendainterlab) && $agendainterlab->valores->count() > 0)
              @foreach($agendainterlab->valores as $key => $valor)
              <div class="row row-valor mt-1 gx-1">
                <div class="col-12 col-md-4">
                  <input type="text" class="form-control" name="valores[{{$key}}][descricao]" placeholder="Descrição" value="{{$valor->descricao}}">
                </div>
                <div class="col-5 col-md-3">
                  <input type="text" class="form-control money" name="valores[{{$key}}][valor]" placeholder="Valor" value="{{$valor->valor}}">
                </div>
                <div class="col-5 col-md-3">
                  <input type="text" class="form-control money" name="valores[{{$key}}][valor_assoc]" placeholder="Valor Associado" value="{{$valor->valor_assoc}}">
                </div>
                <div class="col-2">
                  @if($loop->first)
                  <a href="javascript:void(0)" onclick="duplicateRowValor()"  class="btn btn-primary"> + </a>
                  @endif
                  <a href="javascript:void(0)" onclick="deleteRowValor(this)"  class="btn btn-danger"> - </a>
                </div>
              </div>
              @endforeach
            @else
            <div class="row row-valor mt-1 gx-1">
              <div class="col-12 col-md-6">
                <input type="text" class="form-control" name="valores[0][descricao]" placeholder="Descrição">
              </div>
              <div class="col-5 col-md-2">
                <input type="text" class="form-control money" name="valores[0][valor]" placeholder="Valor">
              </div>
              <div class="col-5 col-md-2">
                <input type="text" class="form-control money" name="valores[0][valor_assoc]" placeholder="Valor Associado">
              </div>
              <div class="col-2">
                <a href="javascript:void(0)" onclick="duplicateRowValor()"  class="btn btn-primary"> + </a>
                <a href="javascript:void(0)" onclick="deleteRowValor(this)"  class="btn btn-danger"> - </a>
              </div>
            </div>
            @endif
          </div>
          <div class="row mt-3">
            <div class="col-12 col-lg-4">
              <x-forms.input-field :value="old('valor_desconto') ?? ($agendainterlab->valor_desconto ?? null)" type="text" name="valor_desconto"
              label="Valor de Desconto " class="money" tooltip="Valor de desconto caso o cliente inscrito em todas as rodadas."/>
              @error('valor_desconto','principal') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
        
      </div>
      
    </div>
    
  </div>

  <div class="row mt-3">
    <div class="col-6 col-lg-3">
      <div class="form-check bg-light rounded mt-4 check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
        <input class="form-check-input" name="inscricao" value="1" id="inscricao" type="checkbox"
          @checked($agendainterlab->inscricao ?? false)>
        <label class="form-check-label" for="inscricao">INSCRIÇÕES</label>
      </div>
      @error('inscricao','principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-6 col-md-4 col-xxl-3">
      <div class="form-check bg-light rounded mt-4 check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
        <input class="form-check-input" name="site" value="1" id="site" type="checkbox"
          @checked($agendainterlab->site ?? false)>
        <label class="form-check-label" for="site">SITE</label>
      </div>
      @error('site','principal') <div class="text-warning">{{ $message }}</div> @enderror
    </div>

    <div class="col-6 col-md-4 col-xxl-3">
      <div class="form-check bg-light rounded mt-4 check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
        <input class="form-check-input" name="destaque" value="1" id="destaque" type="checkbox"
          @checked($agendainterlab->destaque ?? false)>
        <label class="form-check-label" for="destaque">DESTAQUE</label>
      </div>
      @error('destaque','principal') <div class="text-warning">{{ $message }}</div> @enderror
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
      <textarea id="editor" class="ckeditor-classic" name="descricao">{!! old('descricao') ?? ($agendainterlab->descricao ?? null) !!}</textarea>
      @error('descricao','principal') <div class="text-warning">{{ $message }}</div> @enderror
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
  var ckClassicEditor = document.querySelectorAll(".ckeditor-classic");
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

  function reindexValores() {
    const rows = document.querySelectorAll('#valores-wrapper .row-valor');
    rows.forEach(function(row, i) {
      const desc = row.querySelector('input[name*="[descricao]"]');
      const val = row.querySelector('input[name*="[valor]"]');
      const valAssoc = row.querySelector('input[name*="[valor_assoc]"]');

      if (desc) desc.setAttribute('name', `valores[${i}][descricao]`);
      if (val) val.setAttribute('name', `valores[${i}][valor]`);
      if (valAssoc) valAssoc.setAttribute('name', `valores[${i}][valor_assoc]`);
    });
  }

  // Função interna real que remove/limpa a linha
  function deleteRowValorInternal(elem) {
    const wrapper = document.getElementById('valores-wrapper');
    const rows = wrapper ? wrapper.querySelectorAll('.row-valor') : [];

    if (rows.length > 1) {
      const row = elem.closest('.row-valor');
      if (row) row.remove();
      reindexValores();
      atualizarBotoesValores();
    } else {
      const row = elem.closest('.row-valor');
      if (row) {
        Array.from(row.querySelectorAll('input')).forEach(i => i.value = '');
      }
    }
  }

  // Garante que cada linha tenha + e - e associa events
  function atualizarBotoesValores() {
    const rows = document.querySelectorAll('#valores-wrapper .row-valor');
    rows.forEach(function(row) {
      const col2 = row.querySelector('.col-2');
      if (!col2) return;

      // remove anchors existentes para evitar duplicação
      Array.from(col2.querySelectorAll('a')).forEach(a => a.remove());

      // cria botão +
      const addBtn = document.createElement('a');
      addBtn.href = 'javascript:void(0)';
      addBtn.className = 'btn btn-primary me-1';
      addBtn.textContent = '+';
      addBtn.addEventListener('click', function(e) { e.preventDefault(); duplicateRowValor(); });

      // cria botão -
      const delBtn = document.createElement('a');
      delBtn.href = 'javascript:void(0)';
      delBtn.className = 'btn btn-danger';
      delBtn.textContent = '-';
      // chama a função interna
      delBtn.addEventListener('click', function(e) { e.preventDefault(); deleteRowValorInternal(delBtn); });

      col2.appendChild(addBtn);
      col2.appendChild(delBtn);
    });
  }

  // Duplica a última linha (limpa inputs) e reindexa
  function duplicateRowValor() {
    const wrapper = document.getElementById('valores-wrapper');
    if (!wrapper) return;

    const rows = wrapper.querySelectorAll('.row-valor');
    const last = rows[rows.length - 1];
    if (!last) return;

    const clone = last.cloneNode(true);

    // limpa inputs do clone
    Array.from(clone.querySelectorAll('input')).forEach(input => input.value = '');

    // insere após a última linha
    last.after(clone);

    // se houver jQuery.mask disponível, reaplica apenas nos campos do clone
    if (window.jQuery && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.mask === 'function') {
      jQuery(clone).find('.money').mask('000.000.000.000.000,00', { reverse: true });
    }

    reindexValores();
    atualizarBotoesValores();
  }

  function deleteRowValor(elem) {
    // se aqui for chamado com o elemento <a> inline (this), redireciona para a interna
    if (elem && elem.nodeType === 1) {
      deleteRowValorInternal(elem);
    }
  }

  // inicia ao carregar DOM
  document.addEventListener('DOMContentLoaded', function() {
    reindexValores();
    atualizarBotoesValores();

    // reaplica máscara se jQuery.mask estiver presente (opcional)
    if (window.jQuery && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.mask === 'function') {
      jQuery('#valores-wrapper .money').mask('000.000.000.000.000,00', { reverse: true });
    }

    // expõe as funções globalmente caso existam chamadas inline anteriores
    window.duplicateRowValor = duplicateRowValor;
    window.deleteRowValor = function(el) {
      // aceita tanto elemento DOM quanto this passado inline
      if (el && el.nodeType === 1) {
        deleteRowValorInternal(el);
      }
    };
  });

</script>



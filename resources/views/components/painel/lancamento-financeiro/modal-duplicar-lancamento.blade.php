@props([
    'centrosdecusto',
    'planosconta',
    'modalidadepagamento',
    'pessoasModal',
])
@php
  $dupReopenPayload = session('duplicar_lancamento_uid')
      ? [
          'uid' => session('duplicar_lancamento_uid'),
          'data_emissao' => old('data_emissao'),
          'nota_fiscal' => old('nota_fiscal'),
          'consiliacao' => old('consiliacao'),
          'documento' => old('documento'),
          'pessoa_id' => old('pessoa_id'),
          'centro_custo_id' => old('centro_custo_id'),
          'plano_conta_id' => old('plano_conta_id'),
          'historico' => old('historico'),
          'tipo_lancamento' => old('tipo_lancamento'),
          'valor' => old('valor'),
          'data_vencimento' => old('data_vencimento'),
          'data_pagamento' => old('data_pagamento'),
          'modalidade_pagamento_id' => old('modalidade_pagamento_id'),
      ]
      : null;
@endphp

<div class="modal fade" id="modalDuplicarLancamento" tabindex="-1" aria-labelledby="modalDuplicarLancamentoLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="{{ route('lancamento-financeiro-store') }}" id="formDuplicarLancamento">
        @csrf
        <input type="hidden" name="duplicar_lancamento" value="1">
        <input type="hidden" name="lancamento_original_uid" id="dup_lancamento_original_uid" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDuplicarLancamentoLabel">Duplicar lançamento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div class="row gy-3">
            <div class="col-md-3">
              <x-forms.input-field label="Data Emissão" type="date" name="data_emissao" id="dup_data_emissao" :value="null" />
              @error('data_emissao') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field label="Nota Fiscal" type="text" name="nota_fiscal" id="dup_nota_fiscal" :value="null" />
              @error('nota_fiscal') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field label="Conciliação" type="text" name="consiliacao" id="dup_consiliacao" :value="null" />
              @error('consiliacao') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field label="Documento" type="text" name="documento" id="dup_documento" :value="null" />
              @error('documento') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <x-forms.input-select name="pessoa_id" id="dup_pessoa_id" label="Pessoa" required>
                <option value=""> Selecione uma pessoa </option>
                @foreach ($pessoasModal as $pessoa)
                  <option value="{{ $pessoa->id }}">{{ $pessoa->cpf_cnpj }} - {{ Str::limit($pessoa->nome_razao, 50, '...') }}</option>
                @endforeach
              </x-forms.input-select>
              @error('pessoa_id') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-select name="centro_custo_id" id="dup_centro_custo_id" label="Centro Custo" required>
                <option value=""> - </option>
                @foreach ($centrosdecusto as $centrodecusto)
                  <option value="{{ $centrodecusto->id }}">{{ $centrodecusto->descricao }}</option>
                @endforeach
              </x-forms.input-select>
              @error('centro_custo_id') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-select name="plano_conta_id" id="dup_plano_conta_id" label="Plano Conta" required>
                <option value=""> Selecione um plano de conta </option>
                @foreach ($planosconta as $planoconta)
                  <option value="{{ $planoconta->id }}">{{ $planoconta->descricao }}</option>
                @endforeach
              </x-forms.input-select>
              @error('plano_conta_id') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-9">
              <x-forms.input-field label="Histórico" type="text" name="historico" id="dup_historico" :value="null" />
              @error('historico') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-select name="tipo_lancamento" id="dup_tipo_lancamento" label="Tipo" required>
                <option value="CREDITO"> CRÉDITO </option>
                <option value="DEBITO"> DÉBITO </option>
              </x-forms.input-select>
              @error('tipo_lancamento') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
              <x-forms.input-field label="Valor" type="text" name="valor" id="dup_valor" :value="null" />
              @error('valor') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field label="Vencimento" type="date" name="data_vencimento" id="dup_data_vencimento" :value="null" />
              @error('data_vencimento') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field label="Pagamento" type="date" name="data_pagamento" id="dup_data_pagamento" :value="null" />
              @error('data_pagamento') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
              <x-forms.input-select name="modalidade_pagamento_id" id="dup_modalidade_pagamento_id" label="Modalidade de Pagamento">
                <option value=""> - </option>
                @foreach ($modalidadepagamento as $modalidade)
                  <option value="{{ $modalidade->id }}">{{ $modalidade->descricao }}</option>
                @endforeach
              </x-forms.input-select>
              @error('modalidade_pagamento_id') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar cópia</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const dupModalEl = document.getElementById('modalDuplicarLancamento');
    const dupModal = dupModalEl ? new bootstrap.Modal(dupModalEl) : null;

    function definirValorSelect(selectEl, valor) {
      if (!selectEl) {
        return;
      }
      const v = valor === null || valor === undefined || valor === '' ? '' : String(valor);
      selectEl.value = v;
    }

    function preencherModalDuplicar(payload) {
      if (!payload || typeof payload !== 'object') {
        return;
      }
      const uidEl = document.getElementById('dup_lancamento_original_uid');
      if (uidEl) {
        uidEl.value = payload.uid || '';
      }
      const setVal = function (id, v) {
        const el = document.getElementById(id);
        if (el) {
          el.value = v ?? '';
        }
      };
      setVal('dup_data_emissao', payload.data_emissao);
      setVal('dup_nota_fiscal', payload.nota_fiscal);
      setVal('dup_consiliacao', payload.consiliacao);
      setVal('dup_documento', payload.documento);
      definirValorSelect(document.getElementById('dup_pessoa_id'), payload.pessoa_id);
      definirValorSelect(document.getElementById('dup_centro_custo_id'), payload.centro_custo_id);
      definirValorSelect(document.getElementById('dup_plano_conta_id'), payload.plano_conta_id);
      setVal('dup_historico', payload.historico);
      definirValorSelect(document.getElementById('dup_tipo_lancamento'), payload.tipo_lancamento);
      setVal('dup_valor', payload.valor);
      setVal('dup_data_vencimento', payload.data_vencimento);
      setVal('dup_data_pagamento', payload.data_pagamento);
      definirValorSelect(document.getElementById('dup_modalidade_pagamento_id'), payload.modalidade_pagamento_id);
    }

    document.querySelectorAll('.js-abrir-modal-duplicar').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const raw = btn.getAttribute('data-lancamento');
        if (!raw) {
          return;
        }
        try {
          const payload = JSON.parse(raw);
          preencherModalDuplicar(payload);
          if (dupModal) {
            dupModal.show();
          }
        } catch (e) {
          console.error(e);
        }
      });
    });

    const reopenPayload = @json($dupReopenPayload);
    if (reopenPayload && dupModal) {
      preencherModalDuplicar(reopenPayload);
      dupModal.show();
    }
  });
</script>

{{-- modal --}}
<div class="modal fade" id="{{ isset($areaavaliada) ? 'areaAvaliadaModal'.$areaavaliada->uid : 'areaAvaliadaModal' }}" tabindex="-1" aria-labelledby="areaAvaliadaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="areaAvaliadaModalLabel">{{ isset($areaavaliada) ? 'Editar Área de Atuação' : 'Adicionar Área de Atuação' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ route('avaliacao-save-area', $areaavaliada->uid ?? null) }}">
          @csrf
          <div class="row gy-3">
            <input type="hidden" name="avaliacao_id" value="{{ $avaliacao->id }}">
            <input type="hidden" name="area_avaliada_id" value="{{ $areaavaliada->id ?? null }}">
          </div>

          <hr class="my-3">
          <!-- Grupo 1: Área e Situação -->
          <div class="row gy-3">
            <div class="col-12">
              <x-forms.input-select name="area_atuacao_id" label="Área de Atuação" required>
                <option value="">Selecione</option>
                @foreach ($laboratorio->laboratoriosInternos as $labinterno)
                  <option @selected(($areaavaliada->area_atuacao_id ?? null) == $labinterno->area_atuacao_id) value="{{ $labinterno->area_atuacao_id }}">{{ $labinterno->nome }}</option>
                @endforeach
              </x-forms.input-select>
            </div>
            <div class="col-md-4">
              <x-forms.input-select name="situacao" label="Situação" required>
                <option value="">Selecione</option>
                @foreach(['ATIVO','INATIVO','AVALIADOR','AVALIADOR EM TREINAMENTO','AVALIADOR LIDER','ESPECIALISTA'] as $sit)
                  <option @selected(($areaavaliada->situacao ?? null) == $sit) value="{{ $sit }}">{{ $sit }}</option>
                @endforeach
              </x-forms.input-select>
            </div>
          </div>

          <hr class="my-3">
          <!-- Grupo 2: Datas & Dias -->
          <div class="row gy-3">
            <div class="col-md-3">
              <x-forms.input-field name="data_inicial" label="Data Inicial" type="date" value="{{ old('data_inicial') ?? $areaavaliada->data_inicial ?? null }}" />
              @error('data_inicial') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="data_final" label="Data Final" type="date" value="{{ old('data_final') ?? $areaavaliada->data_final ?? null }}" />
              @error('data_final') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="dias" label="Dias" type="number" value="{{ old('dias') ?? $areaavaliada->dias ?? null }}" />
              @error('dias') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
          </div>

          <hr class="my-3">
          <!-- Grupo 3: Avaliador & Ensaios -->
          <div class="row gy-3">
            <div class="col-md-5">
              <x-forms.input-select name="avaliador_id" label="Avaliador" required>
                <option value="">Selecione</option>
                @foreach ($avaliadores as $avaliador)
                  <option @selected(($areaavaliada->avaliador_id ?? null) == $avaliador->id) value="{{ $avaliador->id }}">{{ $avaliador->pessoa->nome_razao }}</option>
                @endforeach
              </x-forms.input-select>
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="num_ensaios" label="Num. Ensaios" type="number" value="{{ old('num_ensaios') ?? $areaavaliada->num_ensaios ?? null }}" />
              @error('num_ensaios') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_dia" label="Valor Dia" class="money" value="{{ old('valor_dia') ?? $areaavaliada->valor_dia ?? null }}" />
              @error('valor_dia') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
          </div>

          <hr class="my-3">
          <!-- Grupo 4: Valores Líder & Avaliador -->
          <div class="row gy-3">
            <div class="col-md-3">
              <x-forms.input-field name="valor_lider" label="Valor Líder" class="money" value="{{ old('valor_lider') ?? $areaavaliada->valor_lider ?? null }}" />
              @error('valor_lider') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_avaliador" label="Valor Avaliador" class="money" readonly placeholder="Calculado ao salvar" value="{{ $areaavaliada->valor_avaliador ?? null }}" />
            </div>
          </div>

          <hr class="my-3">
          <!-- Grupo 5: Deslocamento, Alimentação, Hospedagem, Extras -->
          <div class="row gy-3">
            @foreach([
              'valor_estim_desloc'=>'Estim Desloc', 'valor_real_desloc'=>'Real Desloc',
              'valor_estim_alim'=>'Estim Alim', 'valor_real_alim'=>'Real Alim',
              'valor_estim_hosped'=>'Estim Hospedagem', 'valor_real_hosped'=>'Real Hospedagem',
              'valor_estim_extras'=>'Estim Extras', 'valor_real_extras'=>'Real Extras'
            ] as $field => $label)
              <div class="col-md-3">
                <x-forms.input-field name="{{ $field }}" label="{{ $label }}" class="money" value="{{ old($field) ?? $areaavaliada->{$field} ?? null }}" />
                @error($field) <div class="text-warning">{{ $message }}</div> @enderror
              </div>
            @endforeach
          </div>

          <hr class="my-3">
          <!-- Grupo 6: Totais -->
          <div class="row gy-3">
            <div class="col-md-4">
              <x-forms.input-field name="total_gastos_estim" label="Total Gastos Estim" class="money" readonly placeholder="Auto preenchido" value="{{ $areaavaliada->total_gastos_estim ?? null }}" />
            </div>
            <div class="col-md-4">
              <x-forms.input-field name="total_gastos_reais" label="Total Gastos Reais" class="money" readonly placeholder="Auto preenchido" value="{{ $areaavaliada->total_gastos_reais ?? null }}" />
            </div>
          </div>

          <div class="modal-footer  pt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
{{-- endmodal --}}

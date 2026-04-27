{{-- modal --}}
<div class="modal fade" id="{{ isset($areaavaliada) ? 'areaAvaliadaModal'.$areaavaliada->uid : 'areaAvaliadaModal' }}" tabindex="-1" 
  aria-labelledby="areaAvaliadaModalLabel" aria-hidden="true" x-data="areaAvaliadaData"
  @aa-recalcular-valor-avaliador="calcularValorAvaliador()"
  @aa-recalcular-total-estim="calcularTotalGastosEstim()"
  @aa-recalcular-total-reais="calcularTotalGastosReais()">
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
              <x-forms.input-field name="dias" id="area-avaliada-dias" label="Dias" type="number"
                value="{{ old('dias') ?? $areaavaliada->dias ?? null }}" 
                @input="$dispatch('aa-recalcular-valor-avaliador')" />
              @error('dias') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="num_ensaios" label="Num. Ensaios" type="number" value="{{ old('num_ensaios') ?? $areaavaliada->num_ensaios ?? null }}" />
              @error('num_ensaios') <div class="text-warning">{{ $message }}</div> @enderror
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
          <!-- Grupo 4: Valores Líder & Avaliador -->
          <div class="row gy-3">
            <div class="col-md-3">
              <x-forms.input-field name="valor_dia" id="area-avaliada-valor-dia" label="Valor Dia" class="money"
                value="{{ old('valor_dia') ?? $areaavaliada->valor_dia ?? null }}" 
                @input="$dispatch('aa-recalcular-valor-avaliador')" />
              @error('valor_dia') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
              <x-forms.input-field name="valor_lider" id="area-avaliada-valor-lider" label="Valor Líder" class="money"
                value="{{ old('valor_lider') ?? $areaavaliada->valor_lider ?? null }}" 
                @input="$dispatch('aa-recalcular-valor-avaliador')" />
              @error('valor_lider') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_avaliador" id="area-avaliada-valor-avaliador" label="Valor Avaliador" class="money"
                readonly placeholder="Calculado automaticamente" value="{{ $areaavaliada->valor_avaliador ?? null }}" />
            </div>
          </div>

          <hr class="my-3">
          <!-- Grupo 5: Deslocamento, Alimentação, Hospedagem, Extras -->
          <div class="row gy-3">
            
            <div class="col-md-3">
              <x-forms.input-field name="valor_estim_desloc" id="area-avaliada-valor-estim-desloc" label="Estim Desloc" class="money"
                value="{{ old('valor_estim_desloc') ?? $areaavaliada->valor_estim_desloc ?? null }}" 
                @input="$dispatch('aa-recalcular-total-estim')" />
              @error('valor_estim_desloc') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_estim_alim" id="area-avaliada-valor-estim-alim" label="Estim Alimentação" class="money"
                value="{{ old('valor_estim_alim') ?? $areaavaliada->valor_estim_alim ?? null }}" 
                @input="$dispatch('aa-recalcular-total-estim')" />
              @error('valor_estim_alim') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_estim_hosped" id="area-avaliada-valor-estim-hosped" label="Estim Hospedagem" class="money"
                value="{{ old('valor_estim_hosped') ?? $areaavaliada->valor_estim_hosped ?? null }}" 
                @input="$dispatch('aa-recalcular-total-estim')" />
              @error('valor_estim_hosped') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_estim_extras" id="area-avaliada-valor-estim-extras" label="Estim Extras" class="money"
                value="{{ old('valor_estim_extras') ?? $areaavaliada->valor_estim_extras ?? null }}" 
                @input="$dispatch('aa-recalcular-total-estim')" />
              @error('valor_estim_extras') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
              <x-forms.input-field name="valor_real_desloc" id="area-avaliada-valor-real-desloc" label="Real Desloc" class="money"
                value="{{ old('valor_real_desloc') ?? $areaavaliada->valor_real_desloc ?? null }}" 
                @input="$dispatch('aa-recalcular-total-reais')" />
              @error('valor_real_desloc') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-md-3">
              <x-forms.input-field name="valor_real_alim" id="area-avaliada-valor-real-alim" label="Real Alimentação" class="money"
                value="{{ old('valor_real_alim') ?? $areaavaliada->valor_real_alim ?? null }}" 
                @input="$dispatch('aa-recalcular-total-reais')" />
              @error('valor_real_alim') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-md-3">
              <x-forms.input-field name="valor_real_hosped" id="area-avaliada-valor-real-hosped" label="Real Hospedagem" class="money"
                value="{{ old('valor_real_hosped') ?? $areaavaliada->valor_real_hosped ?? null }}" 
                @input="$dispatch('aa-recalcular-total-reais')" />
              @error('valor_real_hosped') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
              <x-forms.input-field name="valor_real_extras" id="area-avaliada-valor-real-extras" label="Real Extras" class="money"
                value="{{ old('valor_real_extras') ?? $areaavaliada->valor_real_extras ?? null }}" 
                @input="$dispatch('aa-recalcular-total-reais')" />
              @error('valor_real_extras') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
          </div>

          <hr class="my-3">
          <!-- Grupo 6: Totais -->
          <div class="row gy-3">
            <div class="col-md-4">
              <x-forms.input-field name="total_gastos_estim" id="area-avaliada-total-gastos-estim" label="Total Gastos Estim" class="money"
                readonly placeholder="Auto preenchido" value="{{ $areaavaliada->total_gastos_estim ?? null }}" />
            </div>
            <div class="col-md-4">
              <x-forms.input-field name="total_gastos_reais" id="area-avaliada-total-gastos-reais" label="Total Gastos Reais" class="money" readonly placeholder="Auto preenchido" value="{{ $areaavaliada->total_gastos_reais ?? null }}" />
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

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('areaAvaliadaData', () => ({
    // Função auxiliar para converter formato brasileiro para número
    parseMoney(value) {
      if (!value) return 0;
      
      // Converte para string e remove espaços
      let strValue = value.toString().trim();

      // Remove todos os pontos e vírgulas, depois adiciona vírgula nas últimas 2 casas se necessário
      strValue = strValue.replace(/[.,]/g, '');
      
      // Se o valor tem 3 ou mais dígitos, adiciona vírgula nas últimas 2 casas
      if (strValue.length >= 3) {
        strValue = strValue.slice(0, -2) + '.' + strValue.slice(-2);
      }
      const result = parseFloat(strValue);
      return isNaN(result) ? 0 : result;
    },

    getFieldByName(fieldName) {
      return this.$el.querySelector(`[name="${fieldName}"]`);
    },

    calcularValorAvaliador() {
      const valorDiaEl = this.getFieldByName('valor_dia');
      const diasEl = this.getFieldByName('dias');
      const valorLiderEl = this.getFieldByName('valor_lider');
      const valorAvaliadorEl = this.getFieldByName('valor_avaliador');
      if (!valorDiaEl || !diasEl || !valorLiderEl || !valorAvaliadorEl) {
        return;
      }

      const valorDia = this.parseMoney(valorDiaEl.value);
      const dias = parseFloat(diasEl.value) || 0;
      const valorLider = this.parseMoney(valorLiderEl.value);

      if (valorDia > 0 && dias > 0) {
        const resultado = (valorDia * dias) + valorLider;
        valorAvaliadorEl.value = resultado.toFixed(2);
      } else {
        valorAvaliadorEl.value = '';
      }
    },
    
    calcularTotalGastosEstim() {
      const camposEstim = [
        'valor_estim_desloc',
        'valor_estim_alim',
        'valor_estim_hosped',
        'valor_estim_extras',
      ];
      
      let total = 0;
      camposEstim.forEach((campoName) => {
        const campo = this.getFieldByName(campoName);
        const valor = this.parseMoney(campo?.value ?? '');
        total += valor;
      });
      
      const totalGastosEstimEl = this.getFieldByName('total_gastos_estim');
      if (totalGastosEstimEl) {
        totalGastosEstimEl.value = total.toFixed(2);
      }
    },
    
    calcularTotalGastosReais() {
      const camposReais = [
        'valor_real_desloc',
        'valor_real_alim',
        'valor_real_hosped',
        'valor_real_extras',
      ];
      
      let total = 0;
      camposReais.forEach((campoName) => {
        const campo = this.getFieldByName(campoName);
        const valor = this.parseMoney(campo?.value ?? '');
        total += valor;
      });
      
      const totalGastosReaisEl = this.getFieldByName('total_gastos_reais');
      if (totalGastosReaisEl) {
        totalGastosReaisEl.value = total.toFixed(2);
      }
    }
  }));
});
</script>
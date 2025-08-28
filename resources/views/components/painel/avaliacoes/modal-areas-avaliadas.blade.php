{{-- modal --}}
<div class="modal fade" id="{{ isset($areaavaliada) ? 'areaAvaliadaModal'.$areaavaliada->uid : 'areaAvaliadaModal' }}" tabindex="-1" aria-labelledby="areaAvaliadaModalLabel" aria-hidden="true" 
     x-data="{
       calcularValorAvaliador() {
         const valorDia = parseFloat(this.$refs.valorDia.value) || 0;
         const dias = parseFloat(this.$refs.dias.value) || 0;
         const valorLider = parseFloat(this.$refs.valorLider.value) || 0;
         
         if (valorDia > 0 && dias > 0) {
           const resultado = (valorDia * dias) + valorLider;
           this.$refs.valorAvaliador.value = resultado.toFixed(2);
         } else {
           this.$refs.valorAvaliador.value = '';
         }
       },
       
       calcularTotalGastosEstim() {
         const camposEstim = [
           'valor_estim_desloc',
           'valor_estim_alim', 
           'valor_estim_hosped',
           'valor_estim_extras'
         ];
         
         let total = 0;
         camposEstim.forEach(campo => {
           const valor = parseFloat(this.$refs[campo].value) || 0;
           total += valor;
         });
         
         this.$refs.totalGastosEstim.value = total.toFixed(2);
       },
       
       calcularTotalGastosReais() {
         const camposReais = [
           'valor_real_desloc',
           'valor_real_alim', 
           'valor_real_hosped',
           'valor_real_extras'
         ];
         
         let total = 0;
         camposReais.forEach(campo => {
           const valor = parseFloat(this.$refs[campo].value) || 0;
           total += valor;
         });
         
         this.$refs.totalGastosReais.value = total.toFixed(2);
       }
     }">
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
              <x-forms.input-field name="dias" label="Dias" type="number" x-ref="dias" 
                value="{{ old('dias') ?? $areaavaliada->dias ?? null }}" 
                @input="calcularValorAvaliador()" />
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
              <x-forms.input-field name="valor_dia" label="Valor Dia" class="money" x-ref="valorDia" 
                value="{{ old('valor_dia') ?? $areaavaliada->valor_dia ?? null }}" 
                @input="calcularValorAvaliador()" />
              @error('valor_dia') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
          </div>

          <hr class="my-3">
          <!-- Grupo 4: Valores Líder & Avaliador -->
          <div class="row gy-3">
            <div class="col-md-3">
              <x-forms.input-field name="valor_lider" label="Valor Líder" class="money" x-ref="valorLider" 
                value="{{ old('valor_lider') ?? $areaavaliada->valor_lider ?? null }}" 
                @input="calcularValorAvaliador()" />
              @error('valor_lider') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_avaliador" label="Valor Avaliador" class="money" x-ref="valorAvaliador" 
                readonly placeholder="Calculado automaticamente" value="{{ $areaavaliada->valor_avaliador ?? null }}" />
            </div>
          </div>

          <hr class="my-3">
          <!-- Grupo 5: Deslocamento, Alimentação, Hospedagem, Extras -->
          <div class="row gy-3">
            <div class="col-md-3">
              <x-forms.input-field name="valor_estim_desloc" label="Estim Desloc" class="money" x-ref="valor_estim_desloc" 
                value="{{ old('valor_estim_desloc') ?? $areaavaliada->valor_estim_desloc ?? null }}" 
                @input="calcularTotalGastosEstim()" />
              @error('valor_estim_desloc') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_real_desloc" label="Real Desloc" class="money" x-ref="valor_real_desloc" 
                value="{{ old('valor_real_desloc') ?? $areaavaliada->valor_real_desloc ?? null }}" 
                @input="calcularTotalGastosReais()" />
              @error('valor_real_desloc') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_estim_alim" label="Estim Alim" class="money" x-ref="valor_estim_alim" 
                value="{{ old('valor_estim_alim') ?? $areaavaliada->valor_estim_alim ?? null }}" 
                @input="calcularTotalGastosEstim()" />
              @error('valor_estim_alim') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_real_alim" label="Real Alim" class="money" x-ref="valor_real_alim" 
                value="{{ old('valor_real_alim') ?? $areaavaliada->valor_real_alim ?? null }}" 
                @input="calcularTotalGastosReais()" />
              @error('valor_real_alim') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_estim_hosped" label="Estim Hospedagem" class="money" x-ref="valor_estim_hosped" 
                value="{{ old('valor_estim_hosped') ?? $areaavaliada->valor_estim_hosped ?? null }}" 
                @input="calcularTotalGastosEstim()" />
              @error('valor_estim_hosped') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_real_hosped" label="Real Hospedagem" class="money" x-ref="valor_real_hosped" 
                value="{{ old('valor_real_hosped') ?? $areaavaliada->valor_real_hosped ?? null }}" 
                @input="calcularTotalGastosReais()" />
              @error('valor_real_hosped') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_estim_extras" label="Estim Extras" class="money" x-ref="valor_estim_extras" 
                value="{{ old('valor_estim_extras') ?? $areaavaliada->valor_estim_extras ?? null }}" 
                @input="calcularTotalGastosEstim()" />
              @error('valor_estim_extras') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <x-forms.input-field name="valor_real_extras" label="Real Extras" class="money" x-ref="valor_real_extras" 
                value="{{ old('valor_real_extras') ?? $areaavaliada->valor_real_extras ?? null }}" 
                @input="calcularTotalGastosReais()" />
              @error('valor_real_extras') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
          </div>

          <hr class="my-3">
          <!-- Grupo 6: Totais -->
          <div class="row gy-3">
            <div class="col-md-4">
              <x-forms.input-field name="total_gastos_estim" label="Total Gastos Estim" class="money" x-ref="totalGastosEstim" 
                readonly placeholder="Auto preenchido" value="{{ $areaavaliada->total_gastos_estim ?? null }}" />
            </div>
            <div class="col-md-4">
              <x-forms.input-field name="total_gastos_reais" label="Total Gastos Reais" class="money" x-ref="totalGastosReais" readonly placeholder="Auto preenchido" value="{{ $areaavaliada->total_gastos_reais ?? null }}" />
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

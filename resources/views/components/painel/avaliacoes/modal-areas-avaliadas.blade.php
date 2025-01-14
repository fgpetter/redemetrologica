{{-- modal --}}
<div class="modal fade" id="{{ isset($areaavaliada) ? 'areaAvaliadaModal'.$areaavaliada->uid : 'areaAvaliadaModal' }}" tabindex="-1" aria-labelledby="areaAvaliadaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="areaAvaliadaModalLabel"> {{ isset($areaavaliada) ? 'Editar Area de Atuacão' : 'Adicionar Area de Atuacão' }} </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ route('avaliacao-save-area', $areaavaliada->uid ?? null) }}">
            @csrf
            <div class="row gy-3 mb-3">

              <input type="hidden" name="avaliacao_id" value="{{ $avaliacao->id }}">
              <input type="hidden" name="area_avaliada_id" value="{{ $areaavaliada->id ?? null }}">              

              <div class="col-12">
                <x-forms.input-select name="area_atuacao_id" label="Area de Atuação" required>
                  <option value="">Selecione</option>
                  @foreach ($laboratorio->laboratoriosInternos as $labinterno)
                    <option @selected( $areaavaliada->area_atuacao_id ?? null == $labinterno->area_atuacao_id) value="{{ $labinterno->area_atuacao_id }}" >{{ $labinterno->nome }}</option>
                  @endforeach
                </x-forms.input-select>
              </div>

              <div class="col-4">
                <x-forms.input-select name="situacao" label="Situação" required>
                  <option value="">Selecione</option>
                  <option @selected( $areaavaliada->situacao ?? null == 'ATIVO' ) value="ATIVO">ATIVO</option>
                  <option @selected( $areaavaliada->situacao ?? null == 'INATIVO' ) value="INATIVO">INATIVO</option>
                  <option @selected( $areaavaliada->situacao ?? null == 'AVALIADOR' ) value="AVALIADOR">AVALIADOR</option>
                  <option @selected( $areaavaliada->situacao ?? null == 'AVALIADOR EM TREINAMENTO' ) value="AVALIADOR EM TREINAMENTO">AVALIADOR EM TREINAMENTO</option>
                  <option @selected( $areaavaliada->situacao ?? null == 'AVALIADOR LIDER' ) value="AVALIADOR LIDER">AVALIADOR LIDER</option>
                  <option @selected( $areaavaliada->situacao ?? null == 'ESPECIALISTA' ) value="ESPECIALISTA">ESPECIALISTA</option>                  
                </x-forms.input-select>
              </div>

              <div class="col-3">
                <x-forms.input-field name="data_inicial" label="Data Inicial"  type="date" 
                  value="{{ old('data_inicial') ?? $areaavaliada->data_inicial ?? null }}" />
                  @error('data_inicial') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
              
              <div class="col-3">
                <x-forms.input-field name="data_final" label="Data Final"  type="date"
                  value="{{ old('data_final') ?? $areaavaliada->data_final ?? null }}" />
                  @error('data_final') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="dias" label="Dias" type="number"
                  value="{{ old('dias') ?? $areaavaliada->dias ?? null }}" />
                  @error('dias') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-5">
                <x-forms.input-select name="avaliador_id" label="Avaliador" required>
                  <option value="">Selecione</option>
                  @foreach ($avaliadores as $avaliador)
                    <option @selected( $areaavaliada->avaliador_id ?? null == $avaliador->id ) value="{{ $avaliador->id }}" >{{ $avaliador->pessoa->nome_razao }}</option>
                  @endforeach
                </x-forms.input-select>
              </div>

              <div class="col-3">
                <x-forms.input-field name="num_ensaios" label="Num. Ensaios" type="number" 
                  value="{{ old('num_ensaios') ?? $areaavaliada->num_ensaios ?? null }}" />
                  @error('num_ensaios') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_dia" label="Valor Dia" class="money" 
                  value="{{ old('valor_dia') ?? $areaavaliada->valor_dia ?? null }}" />
                  @error('valor_dia') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_lider" label="Valor Líder" class="money" 
                  value="{{ old('valor_lider') ?? $areaavaliada->valor_lider ?? null }}" />
                  @error('valor_lider') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_avaliador" label="Valor Avaliador" class="money" 
                  value="{{ old('valor_avaliador') ?? $areaavaliada->valor_avaliador ?? null }}" />
                  @error('valor_avaliador') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_estim_desloc" label="Estim Desloc" class="money" 
                  value="{{ old('valor_estim_desloc') ?? $areaavaliada->valor_estim_desloc ?? null }}" />
                  @error('valor_estim_desloc') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_real_desloc" label="Real Desloc" class="money" 
                  value="{{ old('valor_real_desloc') ?? $areaavaliada->valor_real_desloc ?? null }}" />
                  @error('valor_real_desloc') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_estim_alim" label="Estim Alim" class="money" 
                  value="{{ old('valor_estim_alim') ?? $areaavaliada->valor_estim_alim ?? null }}" />
                  @error('valor_estim_alim') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_real_alim" label="Real Alim" class="money" 
                  value="{{ old('valor_real_alim') ?? $areaavaliada->valor_real_alim ?? null }}" />
                  @error('valor_real_alim') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_estim_hosped" label="Estim Hospedagem" class="money" 
                  value="{{ old('valor_estim_hosped') ?? $areaavaliada->valor_estim_hosped ?? null }}" />
                  @error('valor_estim_hosped') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_real_hosped" label="Real Hospedagem" class="money" 
                  value="{{ old('valor_real_hosped') ?? $areaavaliada->valor_real_hosped ?? null }}" />
                  @error('valor_real_hosped') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_estim_extras" label="Estim Extras" class="money" 
                  value="{{ old('valor_estim_extras') ?? $areaavaliada->valor_estim_extras ?? null }}" />
                  @error('valor_estim_extras') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_real_extras" label="Real Extras" class="money" 
                  value="{{ old('valor_real_extras') ?? $areaavaliada->valor_real_extras ?? null }}" />
                  @error('valor_real_extras') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3"></div>

              <div class="col-4">
                <x-forms.input-field name="total_gastos_estim" value="{{ $areaavaliada->total_gastos_estim ?? null}}" 
                  label="Total Avaliador Despesas Estim" 
                  class="money"
                  placeholder="Auto preenchido ao salvar"
                  readonly />
              </div>

              <div class="col-4">
                <x-forms.input-field name="total_gastos_reais" value="{{ $areaavaliada->total_gastos_reais ?? null}}" 
                  label="Total Avaliador Despesas Reais" 
                  class="money"
                  placeholder="Auto preenchido ao salvar"
                  readonly />
              </div>
              
  
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  {{-- endmodal --}}
    
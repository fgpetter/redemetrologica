{{-- modal --}}
<div class="modal fade" id="{{ isset($area_avaliada) ? 'areaAvaliadaModal'.$area_avaliada->uid : 'areaAvaliadaModal' }}" tabindex="-1" aria-labelledby="areaAvaliadaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="areaAvaliadaModalLabel">Adicionar Área</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ route('avaliacao-save-area', $area_avaliada->uid ?? null) }}">
            @csrf
            <div class="row gy-3 mb-3">

              <input type="hidden" name="avaliacao_id" value="{{ $avaliacao->id }}">
              <input type="hidden" name="area_avaliada_id" value="{{ $area_avaliada->id ?? null }}">

              <div class="col-12">
                <x-forms.input-select name="area_atuacao_id" label="Area de Atuação" required>
                  <option value="">Selecione</option>
                  @foreach ($laboratorio->laboratoriosInternos as $areaatuacao)
                    <option value="{{ $areaatuacao->id }}" >{{ $areaatuacao->nome }}</option>
                  @endforeach
                </x-forms.input-select>
                @error('area_atuacao_id') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-4">
                <x-forms.input-select name="situacao" label="Situação" required>
                  <option value="">Selecione</option>
                  <option value="ATIVO">ATIVO</option>
                  <option value="INATIVO">INATIVO</option>
                  <option value="AVALIADOR">AVALIADOR</option>
                  <option value="AVALIADOR EM TREINAMENTO">AVALIADOR EM TREINAMENTO</option>
                  <option value="AVALIADOR LIDER">AVALIADOR LIDER</option>
                  <option value="ESPECIALISTA">ESPECIALISTA</option>                  
                </x-forms.input-select>
                @error('situacao') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="data_inicial" label="Nome do laboratório"  type="date" />
                  @error('data_inicial') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
              
              <div class="col-3">
                <x-forms.input-field name="data_final" label="Nome do laboratório"  type="date" />
                  @error('data_final') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="dias" label="Dias" type="number" />
                  @error('dias') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-5">
                <x-forms.input-select name="avaliador_id" label="Avaliador" required>
                  <option value="">Selecione</option>
                  @foreach ($avaliadores as $avaliador)
                    <option value="{{ $avaliador->id }}" >{{ $avaliador->pessoa->nome_razao }}</option>
                  @endforeach
                </x-forms.input-select>
                @error('avaliador_id') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="num_ensaios" label="Num. Ensaios" type="number" />
                  @error('num_ensaios') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_dia" label="Valor Dia" class="money" />
                  @error('valor_dia') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_lider" label="Valor Líder" class="money" />
                  @error('valor_lider') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_avaliador" label="Valor Avaliador" class="money" />
                  @error('valor_avaliador') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_estim_desloc" label="Estim Desloc" class="money" />
                  @error('valor_estim_desloc') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_real_desloc" label="Real Desloc" class="money" />
                  @error('valor_real_desloc') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_estim_alim" label="Estim Alim" class="money" />
                  @error('valor_estim_alim') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_real_alim" label="Real Alim" class="money" />
                  @error('valor_real_alim') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_estim_hosped" label="Estim Hospedagem" class="money" />
                  @error('valor_estim_hosped') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_real_hosped" label="Real Hospedagem" class="money" />
                  @error('valor_real_hosped') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_estim_extras" label="Estim Extras" class="money" />
                  @error('valor_estim_extras') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3">
                <x-forms.input-field name="valor_real_extras" label="Real Extras" class="money" />
                  @error('valor_real_extras') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-3"></div>

              <div class="col-4">
                <x-forms.input-field name="total_gastos_estim" value="{{ $area_avaliada->total_gastos_estim ?? null}}" label="Total Avaliador Despesas Estim" class="money" readonly />
              </div>

              <div class="col-4">
                <x-forms.input-field name="total_gastos_reais" value="{{ $area_avaliada->total_gastos_reais ?? null}}" label="Total Avaliador Despesas Reais" class="money" readonly />
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
    
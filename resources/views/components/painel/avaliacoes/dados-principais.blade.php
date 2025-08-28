<form method="POST" action="{{ route('avaliacao-update', $avaliacao->uid) }}" x-data="{
  updateDataProcLaboratorio() {
    const dataInicio = this.$refs.dataInicio.value;
    if (dataInicio) {
      const data = new Date(dataInicio);
      data.setDate(data.getDate() - 10);
      const dataFormatada = data.toISOString().split('T')[0];
      this.$refs.dataProcLaboratorio.value = dataFormatada;
    }
  },
  updateDataPropostaAcoesCorretivas() {
    const dataFim = this.$refs.dataFim.value;
    if (dataFim) {
      const data = new Date(dataFim);
      
      const dataProposta = new Date(dataFim);
      dataProposta.setDate(dataProposta.getDate() + 7);
      const dataPropostaFormatada = dataProposta.toISOString().split('T')[0];
      this.$refs.dataPropostaAcoesCorretivas.value = dataPropostaFormatada;
      
      const dataAcoes = new Date(dataFim);
      dataAcoes.setDate(dataAcoes.getDate() + 45);
      const dataAcoesFormatada = dataAcoes.toISOString().split('T')[0];
      this.$refs.dataAcoesCorretivas.value = dataAcoesFormatada;
    }
  }
}">
  @csrf

  {{-- Grupo 1: Período & Tipo --}}
  <div class="row gy-3">
    <div class="col-md-3">
      <x-forms.input-field name="data_inicio" :value="old('data_inicio') ?? $avaliacao->data_inicio" 
        label="Data Início" type="date" x-ref="dataInicio" 
        @change="updateDataProcLaboratorio()" />
      @error('data_inicio') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-field name="data_fim" :value="old('data_fim') ?? $avaliacao->data_fim" 
        label="Data Fim" type="date" x-ref="dataFim" 
        @change="updateDataPropostaAcoesCorretivas()" />
      @error('data_fim') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
      <x-forms.input-select name="tipo_avaliacao_id" label="Tipo de Avaliação">
        <option value=""> - </option>
        @foreach ($tipoavaliacao as $tipo)
          <option @selected($avaliacao->tipo_avaliacao_id == $tipo->id) value="{{ $tipo->id }}">{{ $tipo->descricao }}</option>
        @endforeach
      </x-forms.input-select>
    </div>
  </div>


  {{-- Grupo 2: Laboratório & Interno --}}
  <div class="row gy-3">
    <div class="col-12 col-xxl-7">
      <label for="laboratorio" class="f">Laboratório</label>
      <input type="text" class="form-control" id="laboratorio" name="laboratorio" value="{{ $laboratorio->nome_laboratorio }} - {{ $laboratorio->pessoa->nome_razao }}" readonly>
      @error('laboratorio') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-xxl-5">
      <x-forms.input-select name="laboratorio_interno_id" label="Laboratório Interno">
        <option value=""> - </option>
        @foreach ($laboratorio->laboratoriosInternos as $labinterno)
          <option @selected($avaliacao->laboratorio_interno_id == $labinterno->id) value="{{ $labinterno->id }}">{{ $labinterno->nome }}</option>
        @endforeach
      </x-forms.input-select>
    </div>
  </div>



  {{-- Grupo 3: Contato --}}
  <div class="row mt-3">
    <div class="col-md-3">
      <label for="contato" class="form-label">Contato</label>
      <input type="text" class="form-control" id="contato" name="contato" value="{{ $laboratorio->contato }}" readonly>
    </div>
    <div class="col-md-3">
      <label for="fone" class="form-label">Telefone</label>
      <input type="text" class="form-control" id="fone" name="fone" value="{{ $laboratorio->telefone }}" readonly>
    </div>
    <div class="col-md-3">
      <label for="email" class="form-label">E-mail</label>
      <input type="email" class="form-control" id="email" name="email" value="{{ $laboratorio->email }}" readonly>
      @error('email') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">&nbsp;</div>
  </div>



  {{-- Grupo 4: Proposta Financeira --}}
  <div class="row mt-3">
    <div class="col-md-3">
      <x-forms.input-field name="valor_proposta" :value="old('valor_proposta') ?? $avaliacao->valor_proposta" label="Valor Proposta" class="money" />
      @error('valor_proposta') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-select name="status_proposta" label="Status da Proposta">
        @foreach (['PENDENTE','AGUARDANDO','APROVADA','REPROVADA'] as $status)
          <option @selected($avaliacao->status_proposta == $status) value="{{ $status }}">{{ ucfirst(strtolower($status)) }}</option>
        @endforeach
      </x-forms.input-select>
    </div>
    <div class="col-md-6"></div>
  </div>

  <hr class="my-3">

  {{-- Grupo 5: Flags FR --}}
  <div class="row mt-3">
    @foreach (['fr_28'=>'FR 28', 'fr_41'=>'FR 41', 'fr_101'=>'FR 101', 'fr_48'=>'FR 48'] as $field => $label)
      <div class="col-md-3">
        <x-forms.input-select name="{{ $field }}" label="{{ $label }}">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->$field) value="1">SIM</option>
        </x-forms.input-select>
      </div>
    @endforeach
  </div>



  {{-- Grupo 6: Relatório & Procedimentos --}}
  <div class="row mt-3">
    <div class="col-md-3">
      <x-forms.input-select name="relatorio_fr06" label="Relatório FR06">
        @foreach([
          'INCOMPLETA','COMPLETA','ENVIADA TODOS','ENVIADA AVALIADORES',
          'ENVIADA LABORATORIO','NAO ENVIADA COMPLETA','APROVADA TODOS',
          'APROVADA AVALIADORES','NAO APROVADA'
        ] as $opt)
          <option @selected($avaliacao->relatorio_fr06 == $opt) value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
      </x-forms.input-select>
    </div>
    <div class="col-md-3">
      <x-forms.input-field name="data_proc_laboratorio" :value="old('data_proc_laboratorio') ?? $avaliacao->data_proc_laboratorio" 
        label="Data Procedim. Laboratório" type="date" x-ref="dataProcLaboratorio" />
      @error('data_proc_laboratorio') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-select name="proc_laboratorio" label="Procedim. Laboratório">
        <option value="0">NÃO</option>
        <option @selected($avaliacao->proc_laboratorio) value="1">SIM</option>
      </x-forms.input-select>
    </div>
    <div class="col-md-3"></div>
  </div>



  {{-- Grupo 7: Info Avaliadores & Carta --}}
  <div class="row mt-3">
    <div class="col-md-3">
      <x-forms.input-select name="inf_avaliadores" label="Inf. Avaliadores">
        <option value="0">NÃO</option>
        <option @selected($avaliacao->inf_avaliadores) value="1">SIM</option>
      </x-forms.input-select>
    </div>
    <div class="col-md-3">
      <x-forms.input-select name="carta_reconhecimento" label="Carta Reconhecimento">
        <option value="0">NÃO</option>
        <option @selected($avaliacao->carta_reconhecimento) value="1">SIM</option>
      </x-forms.input-select>
    </div>
    <div class="col-md-6"></div>
  </div>

  <hr class="my-3">

  {{-- Grupo 8: Pesquisa & Métricas --}}
  <div class="row mt-3">
    <div class="col-md-3">
      <x-forms.input-field name="retorno_fr06" :value="old('retorno_fr06') ?? $avaliacao->retorno_fr06" label="Retorno FR06" type="date" />
      @error('retorno_fr06') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-field name="pesq_satisfacao" :value="old('pesq_satisfacao') ?? $avaliacao->pesq_sativacao" label="Pesquisa Satisfação" type="date" />
      @error('pesq_satisfacao') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-field name="med_pesquisa" :value="old('med_pesquisa') ?? $avaliacao->med_pesquisa" label="Média Pesquisa" readonly />
      @error('med_pesquisa') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3"></div>
  </div>

 

  {{-- Grupo 9: Ações Corretivas --}}
  <div class="row mt-3">
    <div class="col-md-3">
      <x-forms.input-field name="data_proposta_acoes_corretivas" :value="old('data_proposta_acoes_corretivas') ?? $avaliacao->data_proposta_acoes_corretivas" 
        label="Data Proposta Ações Corretivas" type="date" x-ref="dataPropostaAcoesCorretivas" />
      @error('data_proposta_acoes_corretivas') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-field name="data_acoes_corretivas" :value="old('data_acoes_corretivas') ?? $avaliacao->data_acoes_corretivas" 
        label="Data Ações Corretivas" type="date" x-ref="dataAcoesCorretivas" />
      @error('data_acoes_corretivas') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-select name="acoes_aceitas" label="Ações Aceitas">
        <option value=""> - </option>
        @foreach(['SIM','NAO','PARCIALMENTE'] as $opt)
          <option @selected($avaliacao->acoes_aceitas == $opt) value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
      </x-forms.input-select>
      @error('acoes_aceitas') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3"></div>
  </div>



  {{-- Grupo 10: Comitê --}}
  <div class="row mt-3">
    <div class="col-md-3">
      <x-forms.input-field name="data_reuniao_comite" :value="old('data_reuniao_comite') ?? $avaliacao->data_reuniao_comite" label="Data Reunião Comitê" type="date" />
      @error('data_reuniao_comite') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-select name="comite" label="Resultado Comitê">
        <option value=""> - </option>
        @foreach(['APROVADO','NAO APROVADO','COM PENDENCIAS'] as $opt)
          <option @selected($avaliacao->comite == $opt) value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
      </x-forms.input-select>
    </div>
    <div class="col-md-3">
      <x-forms.input-field name="prazo_ajuste_pos_comite" :value="old('prazo_ajuste_pos_comite') ?? $avaliacao->prazo_ajuste_pos_comite" label="Prazo Ajustes Pós Comitê" type="date" />
      @error('prazo_ajuste_pos_comite') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3"></div>
  </div>

  <hr class="my-3">

  {{-- Grupo 11: Certificado & Publicação --}}
  <div class="row gy-3">
    <div class="col-md-3">
      <x-forms.input-select name="certificado" label="Certificado">
        <option value="0">NÃO</option>
        <option @selected($avaliacao->certificado) value="1">SIM</option>
      </x-forms.input-select>
    </div>
    <div class="col-md-3">
      <x-forms.input-field name="validade_certificado" :value="old('validade_certificado') ?? $avaliacao->validade_certificado" label="Validade Certificado BRANCO" type="date" />
      @error('validade_certificado') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-select name="enviado_certificado" label="Certificado Enviado">
        <option value=""> - </option>
        @foreach(['ENVIADO','NAO ENVIADO','PENDENTE'] as $opt)
          <option @selected($avaliacao->enviado_certificado == $opt) value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
      </x-forms.input-select>
    </div>
    <div class="col-md-3"></div>

    <div class="col-md-3">
      <x-forms.input-field name="data_publicacao_site" :value="old('data_publicacao_site') ?? $avaliacao->data_publicacao_site" label="Publicação Site" type="date" />
      @error('data_publicacao_site') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
      <x-forms.input-select name="certificado_impresso" label="Certificado Impresso">
        <option value="0">NÃO</option>
        <option @selected($avaliacao->certificado_impresso) value="1">SIM</option>
      </x-forms.input-select>
    </div>
    <div class="col-md-3">
      <x-forms.input-field name="ano_revisao_certificado" :value="old('ano_revisao_certificado') ?? $avaliacao->ano_revisao_certificado" label="Revisão Certificado PRETO" type="date" />
      @error('ano_revisao_certificado') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3"></div>
  </div>

  <hr class="my-3">

  {{-- Observações --}}
  <div class="row">
    <div class="col-12">
      <x-forms.input-textarea name="obs" label="Observações">
        {{ old('obs') ?? $avaliacao->obs }}
      </x-forms.input-textarea>
      @error('obs') <div class="text-warning">{{ $message }}</div> @enderror
    </div>
  </div>

 

  {{-- Ações finais: Salvar & Delete --}}
  <div class="row mt-4" >
    <div class="col-12 d-flex justify-content-between">
      <button type="submit" class="btn btn-primary px-4">Salvar</button>
      <x-painel.laboratorios.form-delete route="avaliacao-delete" id="{{ $avaliacao->uid }}" />
    </div>
  </div>
</form>

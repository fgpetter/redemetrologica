<form method="POST" action="{{ route('avaliacao-update', $avaliacao->uid) }}">
  @csrf
    <div class="row gy-3">

      <div class="col-3">
        <x-forms.input-field name="data_inicio" :value="old('data_inicio') ?? $avaliacao->data_inicio ?? null" label="Data Inicio" type="date" />
          @error('data_inicio') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="data_fim" :value="old('data_fim') ?? $avaliacao->data_fim ?? null" label="Data Fim" type="date" />
          @error('data_fim') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-4">
        <x-forms.input-select name="tipo_avaliacao_id" label="Tipo Avaliacao">
          <option value=""> - </option>
          @foreach ($tipoavaliacao as $tipo)
            <option @selected($avaliacao->tipo_avaliacao_id == $tipo->id) value="{{ $tipo->id }}">{{ $tipo->descricao }}</option>
          @endforeach
        </x-forms.input-select>
        @error('tipo_avaliacao_id') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-xxl-7">
        <label for="laboratorio" class="form-label">Laboratório</label>
        <input type="text" class="form-control" name="laboratorio" value="{{ $laboratorio->nome_laboratorio }} - {{ $laboratorio->pessoa->nome_razao }}" id="laboratorio" readonly>
        @error('laboratorio') <div class="text-warning">{{ $message }}</div> @enderror        
      </div>

      <div class="col-12 col-xxl-5">
        <x-forms.input-select name="laboratorio_interno_id" label="Laboratorio Interno">
          <option value=""> - </option>
          @foreach ($laboratorio->laboratoriosInternos as $labinterno)
            <option @selected($avaliacao->laboratorio_interno_id == $labinterno->id) value="{{ $labinterno->id }}">{{ $labinterno->nome }}</option>
          @endforeach
        </x-forms.input-select>
        @error('laboratorio_interno_id') <div class="text-warning">{{ $message }}</div> @enderror
        
      </div>

      <div class="col-3">
        <label for="contato" class="form-label">Contato</label>
        <input type="text" class="form-control" name="contato" value="{{ $laboratorio->contato }}" id="contato">        
      </div>

      <div class="col-3">
        <label for="fone" class="form-label">Telefone</label>
        <input type="text" class="form-control" name="fone" value="{{ $laboratorio->telefone }}" id="fone">        
      </div>

      <div class="col-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="text" class="form-control" name="email" value="{{ $laboratorio->email }}" id="fone">        
      </div>

      <div class="col-3"> &nbsp; </div>


      <div class="col-3">
        <x-forms.input-field name="valor_proposta" :value="old('valor_proposta') ?? $avaliacao->valor_proposta ?? null" label="Valor Proposta" class="money" />
          @error('valor_proposta') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="status_proposta" label="Status da proposta">
          <option @selected($avaliacao->status_proposta == 'PENDENTE') value="PENDENTE">PENDENTE</option>
          <option @selected($avaliacao->status_proposta == 'AGUARDANDO') value="AGUARDANDO">AGUARDANDO</option>
          <option @selected($avaliacao->status_proposta == 'APROVADA') value="APROVADA">APROVADA</option>
          <option @selected($avaliacao->status_proposta == 'REPROVADA') value="REPROVADA">REPROVADA</option>
        </x-forms.input-select>
        @error('status_proposta') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
      
      <div class="col-6"> &nbsp; </div>

      <div class="col-3">
        <x-forms.input-select name="fr_28" label="FR 28">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->fr_28) value="1">SIM</option>
        </x-forms.input-select>
        @error('fr_28') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="fr_41" label="FR 41">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->fr_41) value="1">SIM</option>
        </x-forms.input-select>
        @error('fr_41') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="fr_101" label="FR 101">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->fr_101) value="1">SIM</option>
        </x-forms.input-select>
        @error('fr_101') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="fr_48" label="FR 48">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->fr_48) value="1">SIM</option>
        </x-forms.input-select>
        @error('fr_48') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="relatorio_fr06" label="Relatório FR6">
          <option @selected($avaliacao->status_proposta == 'INCOMPLETA') value="INCOMPLETA">INCOMPLETA</option>
          <option @selected($avaliacao->status_proposta == 'COMPLETA') value="COMPLETA">COMPLETA</option>
          <option @selected($avaliacao->status_proposta == 'ENVIADA TODOS') value="ENVIADA TODOS">ENVIADA TODOS</option>
          <option @selected($avaliacao->status_proposta == 'ENVIADA AVALIADORES') value="ENVIADA AVALIADORES">ENVIADA AVALIADORES</option>
          <option @selected($avaliacao->status_proposta == 'ENVIADA LABORATORIO') value="ENVIADA LABORATORIO">ENVIADA LABORATORIO</option>
          <option @selected($avaliacao->status_proposta == 'NAO ENVIADA COMPLETA') value="NAO ENVIADA COMPLETA">NAO ENVIADA COMPLETA</option>
          <option @selected($avaliacao->status_proposta == 'APROVADA TODOS') value="APROVADA TODOS">APROVADA TODOS</option>
          <option @selected($avaliacao->status_proposta == 'APROVADA AVALIADORES') value="APROVADA AVALIADORES">APROVADA AVALIADORES</option>
          <option @selected($avaliacao->status_proposta == 'NAO APROVADA') value="NAO APROVADA">NAO APROVADA</option>
        </x-forms.input-select>
        @error('relatorio_fr06') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="data_proc_laboratorio" :value="old('data_proc_laboratorio') ?? $avaliacao->data_proc_laboratorio ?? null" label="Data Procedim. Laboratório" type="date" />
        @error('data_proc_laboratorio') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="proc_laboratorio" label="Procedim. Laboratório">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->proc_laboratorio) value="1">SIM</option>
        </x-forms.input-select>
        @error('proc_laboratorio') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="inf_avaliadores" label="Inf. Avaliadores">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->inf_avaliadores) value="1">SIM</option>
        </x-forms.input-select>
        @error('inf_avaliadores') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="carta_reconhecimento" label="Carta Reconhecimento">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->carta_reconhecimento) value="1">SIM</option>
        </x-forms.input-select>
        @error('carta_reconhecimento') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="retorno_fr06" :value="old('retorno_fr06') ?? $avaliacao->retorno_fr06 ?? null" label="Retorno FR06" type="date" />
        @error('retorno_fr06') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="pesq_satisfacao" :value="old('pesq_satisfacao') ?? $avaliacao->pesq_satisfacao ?? null" label="Pesquisa Satisfação" type="date" />
        @error('pesq_satisfacao') <div class="text-warning">{{ $message }}</div> @enderror          
      </div>

      <div class="col-3">
        <x-forms.input-field name="med_pesquisa" :value="old('med_pesquisa') ?? $avaliacao->med_pesquisa ?? null" label="Média Pesquisa" readonly />
        @error('med_pesquisa') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="data_proposta_acoes_corretivas" :value="old('data_proposta_acoes_corretivas') ?? $avaliacao->data_proposta_acoes_corretivas ?? null" 
          label="Data Proposta Ações Corretivas" type="date" />
        @error('data_proposta_acoes_corretivas') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="data_acoes_corretivas" :value="old('data_acoes_corretivas') ?? $avaliacao->data_acoes_corretivas ?? null" 
          label="Data Ações Corretivas" type="date" />
        @error('data_acoes_corretivas') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="acoes_aceitas" label="Ações Aceitas">
          <option > - </option>
          <option @selected($avaliacao->acoes_aceitas = "SIM") value="SIM">SIM</option>
          <option @selected($avaliacao->acoes_aceitas = "NAO") value="NAO">NÃO</option>
          <option @selected($avaliacao->acoes_aceitas = "PARCIALMENTE") value="PARCIALMENTE">PARCIALMENTE</option>
        </x-forms.input-select>
        @error('acoes_aceitas') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="data_reuniao_comite" :value="old('data_reuniao_comite') ?? $avaliacao->data_reuniao_comite ?? null" 
          label="Data Reunião Comitê" type="date" />
        @error('data_reuniao_comite') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="comite" label="Reunião Comitê">
          <option > - </option>
          <option @selected($avaliacao->comite = "APROVADO") value="APROVADO">APROVADO</option>
          <option @selected($avaliacao->comite = "NAO APROVADO") value="NAO APROVADO">NÃO APROVADO</option>
          <option @selected($avaliacao->comite = "COM PENDENCIAS") value="COM PENDENCIAS">COM PENDÊNCIAS</option>
        </x-forms.input-select>
        @error('comite') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="prazo_ajuste_pos_comite" :value="old('prazo_ajuste_pos_comite') ?? $avaliacao->prazo_ajuste_pos_comite ?? null" 
          label="Prazo ajustes pos comitê" type="date" />
        @error('prazo_ajuste_pos_comite') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="certificado" label="Certificado">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->certificado) value="1">SIM</option>
        </x-forms.input-select>
        @error('certificado') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="validade_certificado" :value="old('validade_certificado') ?? $avaliacao->validade_certificado ?? null" 
          label="Validade Certificado BRANCO" type="date" />
        @error('validade_certificado') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="enviado_certificado" label="Ações Aceitas">
          <option > - </option>
          <option @selected($avaliacao->enviado_certificado = "ENVIADO") value="ENVIADO">ENVIADO</option>
          <option @selected($avaliacao->enviado_certificado = "NAO ENVIADO") value="NAO ENVIADO">NÃO ENVIADO</option>
          <option @selected($avaliacao->enviado_certificado = "PENDENTE") value="PENDENTE">PENDENTE</option>
        </x-forms.input-select>
        @error('enviado_certificado') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="data_publicacao_site" :value="old('data_publicacao_site') ?? $avaliacao->data_publicacao_site ?? null" 
          label="Publicação Site" type="date" />
        @error('data_publicacao_site') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-select name="certificado_impresso" label="Certificado Impresso">
          <option value="0">NÃO</option>
          <option @selected($avaliacao->certificado_impresso) value="1">SIM</option>
        </x-forms.input-select>
        @error('certificado_impresso') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-3">
        <x-forms.input-field name="ano_revisao_certificado" :value="old('ano_revisao_certificado') ?? $avaliacao->ano_revisao_certificado ?? null" 
          label="Revisão Certificado PRETO" type="date" />
        @error('ano_revisao_certificado') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <x-forms.input-textarea name="obs" label="Observações">
          {{ old('obs') ?? $avaliacao->obs ?? null }}
        </x-forms.input-textarea>
        @error('obs') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
      </div>

    </div>
</form>

<div class="col-12 d-flex justify-content-end">
  <x-painel.laboratorios.form-delete route="avaliacao-delete" id="{{ $avaliacao->uid }}" />
</div>

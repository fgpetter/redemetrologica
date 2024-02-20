<div class="row gy-3">
  <div class="col-sm-6">
    <x-forms.input-field :value="old('resp_info') ?? ($agendamento_curso->resp_info ?? null)" name="resp_info"
      label="Responsável pelas Informações" placeholder="" />
    @error('resp_info')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-3">
    <x-forms.input-field :value="old('inserido_em') ?? ($agendamento_curso->inserido_em ?? null)" type="date" name="inserido_em"
      label="Inserido em" />
    @error('inserido_em')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-3">
    <x-forms.input-field :value="old('alterado_em') ?? ($agendamento_curso->alterado_em ?? null)" type="date" name="alterado_em"
      label="Alterado em" />
    @error('alterado_em')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-12">
    <x-forms.input-field :value="old('razao_social') ?? ($agendamento_curso->razao_social ?? null)" name="razao_social" label="Razão Social"
      placeholder="" />
    @error('razao_social')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-12">
    <x-forms.input-field :value="old('email') ?? ($agendamento_curso->email ?? null)" name="email" label="Email" placeholder="" />
    @error('email')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-4">
    <x-forms.input-field :value="old('cnpj_cpf') ?? ($agendamento_curso->cnpj_cpf ?? null)" name="cnpj_cpf" label="cnpj_cpf"
      placeholder="" />
    @error('CNPJ/CPF')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-4">
    <x-forms.input-field :value="old('ie') ?? ($agendamento_curso->ie ?? null)" name="ie" label="IE" placeholder="" />
    @error('ie')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-4">
    <x-forms.input-field :value="old('im') ?? ($agendamento_curso->im ?? null)" name="im" label="Insc. Municipal"
      placeholder="" />
    @error('im')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-12">
    <x-forms.input-field :value="old('endereco') ?? ($agendamento_curso->endereco ?? null)" name="endereco" label="Endereço"
      placeholder="" />
    @error('endereco')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-4">
    <x-forms.input-field :value="old('Bairro') ?? ($agendamento_curso->Bairro ?? null)" name="Bairro" label="Bairro" placeholder="" />
    @error('Bairro')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-4">
    <x-forms.input-field :value="old('Cidade') ?? ($agendamento_curso->Cidade ?? null)" name="Cidade" label="Cidade" placeholder="" />
    @error('Cidade')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-1">
    <x-forms.input-field :value="old('UF') ?? ($agendamento_curso->UF ?? null)" name="UF" label="UF" placeholder="" />
    @error('UF')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-3">
    <x-forms.input-field :value="old('CEP') ?? ($agendamento_curso->CEP ?? null)" name="CEP" label="CEP" placeholder="" />
    @error('CEP')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-4">
    <x-forms.input-field :value="old('fone') ?? ($agendamento_curso->fone ?? null)" name="fone" label="fone" placeholder="" />
    @error('fone')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-4">
    <x-forms.input-select name="enviado_financ" label="Enviado Financeiro">

      <option value="SIM">SIM</option>
      <option value="NAO">NÃO</option>


    </x-forms.input-select>
    @error('enviado_financ')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-4">
    <x-forms.input-field :value="old('data_doc') ?? ($agendamento_curso->data_doc ?? null)" type="date" name="data_doc"
      label="Data Documento" />
    @error('data_doc')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-4">
    <x-forms.input-field :value="old('valor_NF') ?? ($agendamento_curso->valor_NF ?? null)" name="valor_NF" label="Valor da Nota"
      placeholder="" />
    @error('valor_NF')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-4">
    <x-forms.input-field :value="old('num_NF') ?? ($agendamento_curso->num_NF ?? null)" name="num_NF" label="Num NF" placeholder="" />
    @error('num_NF')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-4">
    <x-forms.input-field :value="old('data_pagamento') ?? ($agendamento_curso->data_pagamento ?? null)" type="date" name="data_pagamento"
      label="Data de Pagamento" />
    @error('data_pagamento')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-sm-4">
    <x-forms.input-select name="modalidade" label="Modalidade">

      <option value="Modalidade 1">Modalidade 1</option>
      <option value="Modalidade 2">Modalidade 2</option>


    </x-forms.input-select>
    @error('modalidade')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-sm-12">
    <x-forms.input-textarea name="obs" label="Observações">
      {{ old('obs') ?? ($agendamento_curso->obs ?? null) }}
    </x-forms.input-textarea>
    @error('obs')
      <div class="text-warning">{{ $message }}</div>
    @enderror
  </div>

  {{-- lista --}}
  {{-- PLACEHOLDER --> trocar --}}
  <div class="row">
    <div class="col">
      {{-- <x-painel.agendamento-cursos.list-notas /> --}}
    </div>
  </div>
  {{-- PLACEHOLDER --> trocar --}}

  {{-- lista --}}


  {{-- conteudo --}}

</div>

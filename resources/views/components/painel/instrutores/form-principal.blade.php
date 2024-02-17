<form method="POST" action="{{ isset($funcionario->uid) ? route('funcionario-update', $funcionario->uid) : route('funcionario-create') }}" enctype="multipart/form-data">
    @csrf
    <div class="row gy-3 mt-3">

        <div class="col-sm-8">
            <x-forms.input-field :value="old('nome') ?? ($instrutor->nome ?? null)" name="nome" label="Nome" placeholder="" />
            @error('nome')
            <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-sm-2">
            <x-forms.input-select name="situacao" label="Situação">
              <option value="1">ATIVO</option>
              <option value="0">INATIVO</option>
            </x-forms.input-select>
            @error('situacao')<div class="text-warning">{{ $message }}</div>@enderror
        </div>

        <div class="col-sm-2">
            <x-forms.input-select name="tipo_pessoa" label="Tipo Pessoa">
              <option value="PF">PESSOA FÍSICA</option>
              <option value="PJ">PESSOA JURÍDICA</option>
            </x-forms.input-select>
            @error('tipo_pessoa')
            <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-sm-3">
            <x-forms.input-field :value="old('cnpj_cpf') ?? ($instrutor->cnpj_cpf ?? null)" name="cnpj" label="CNPJ/CPF" placeholder="CNPJ/CPF" />
            @error('cnpj_cpf')
            <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-3">
            <x-forms.input-field :value="old('rg_IE') ?? ($instrutor->rg_ie ?? null)" name="rg_ie" label="RG/IE" placeholder="RG/IE" />
            @error('rg_ie')
            <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-sm-6">
          @if (isset($instrutor->curriculo) && $instrutor->curriculo )
            <div class="input-group mt-4">
              <input type="text" class="form-control" readonly value="{{ explode("curriculos/", $instrutor->curriculo)[1] }}" >
              <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ asset($instrutor->curriculo) }}" target="_blank">Baixar</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="javascript:void(0)" 
                    onclick="document.getElementById('curriculo-delete').submit();">Remover
                  </a>
                </li>
              </ul>
            </div>
          @else
            <label for="curriculo" class="form-label">Currículo</label>
            <input class="form-control" name="curriculo" type="file" id="curriculo" accept=".doc, .pdf, .docx">
            @error('curriculo') <div class="text-warning">{{ $message }}</div> @enderror
          @endif
        </div>
        <h6 class="mb-0 mt-4">Dados de endereço</h6>
          <x-painel.enderecos.form-endereco :endereco="$funcionario->pessoa->enderecos[0] ?? null"/>

        <h6 class="mb-0 mt-4">Dados bancários</h6>
        <div class="col-sm-6">
            <x-forms.input-field :value="old('banco') ?? ($instrutor->banco ?? null)" name="banco" label="Banco" placeholder="" />
            @error('banco')
            <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-6">
            <x-forms.input-field :value="old('agencia') ?? ($instrutor->agencia ?? null)" name="agencia" label="Agência" placeholder="" />
            @error('agencia')
            <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-6">
            <x-forms.input-field :value="old('conta') ?? ($instrutor->conta ?? null)" name="conta" label="Conta" placeholder="" />
            @error('conta')
            <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-12">
            <x-forms.input-textarea name="observacoes" label="Observações">
                {{ old('observacoes') ?? ($instrutor->observacoes ?? null) }}
            </x-forms.input-textarea>
            @error('observacoes')
            <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <!-- Btn -->
    <div class="row mt-3">
        <div class="col-sm-12">
            <button type="submit" class="btn btn-primary px-4"> Cadastrar
            </button>
        </div>
    </div>
</form>

<div class="col-12 col-xxl-7 col-xl-8">

  <div class="card">
    <div class="card-body">
      <h5 class="card-subtitle mt-3 mb-2 text-primary-emphasis">Dados do interlaboratorial:</h5>

      <p class="pb-3">
        <strong>Interlaboratorial:</strong> {{ $interlab->interlab->nome }} <br>
        <strong>Agenda:</strong> de {{ \Carbon\Carbon::parse($interlab->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($interlab->data_fim)->format('d/m/Y') }} <br>
      </p>
      @if($interlab->instrucoes_inscricao)
        <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-5">
          <i class="ri-information-fill text-primary fs-5"></i> Importante:
          <p class="mb-2 text-black fs-6 fs-sm-5">{!! nl2br($interlab->instrucoes_inscricao) !!}</p>
        </blockquote>
      @endif

      @if(!$empresa)
        <form action="{{ route('informa-empresa-interlab') }}" method="post" class="mb-5">
          @csrf
            <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-warning rounded">
              <p class="mb-2 text-black">Para prosseguir, você precisa informar o CNPJ da empresa participante.</p>
            </blockquote>
            <div class="row">
              <div class="col-8 col-xxl-6">
                <input type="text" class="form-control" name="cnpj" placeholder="CNPJ" id="input-cnpj">
                @error('cnpj')<div class="text-warning">{{ $message }}</div>@enderror
              </div>
              <div class="col-2">
                <button type="submit" class="btn btn-primary">Adicionar</button>
              </div>
            </div>
        </form>
      @endif
      
      @if($empresa )
        <div class="mt-3 mb-5">
          <h5 class="card-subtitle mb-2 text-primary-emphasis">Dados da empresa participante:</h5>
          <p>
            <strong>Razaão social:</strong> {{ $empresa->nome_razao }} <br>
            <strong>CNPJ:</strong> {{ $empresa->cpf_cnpj }} <br>
          </p>
        </div>

        @if( $inscritos->count() > 0 )
          <div class="card bg-light shadow-none">
            <h6 class="card-title mb-0 px-3 pt-2 pb-0">Laboratórios inscritos:</h6>
            <div class="card-body pt-2">
              @foreach ($inscritos as $inscrito)
                <div class="{{ ($loop->index > 0) ? "border-top border-dark pt-3" : ""}}" >
                  <span class="fs-5">{{ $inscrito->laboratorio->nome }}</span> <br>
                  <strong>Responsável Técnico</strong> {{ $inscrito->laboratorio->responsavel_tecnico }} <br>
                  <strong>Telefone</strong> {{ $inscrito->laboratorio->telefone }} <br>
                  <strong>Email</strong> {{ $inscrito->laboratorio->email }} <br>
                  <strong>Informações de inscrição:</strong>
                  <p class="ps-3" >{!! nl2br($inscrito->informacoes_inscricao) !!}</p>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        <form action="{{ route('confirma-inscricao-interlab') }}" method="post" id="confirma-inscricao-interlab" novalidate>
          @csrf
          <input type="hidden" name="encerra_cadastro" value="0" id="encerra_cadastro">
          <input type="hidden" name="empresa_uid" value="{{ $empresa->uid }}">
          <input type="hidden" name="interlab_uid" value="{{ $interlab->uid }}">
          <div class="card border overflow-hidden card-border-dark shadow-none">
            <div class="card-header">
              <h6 class="card-title mb-0">Adicionar Laboratorio:</h6>
            </div>
            <div class="card-body">
              <x-forms.input-field :value="old('laboratorio') ?? null" name="laboratorio" label="Laboratório" class="mb-2" required/>
              @error('laboratorio') <div class="text-warning">{{ $message }}</div> @enderror

              <x-forms.input-field :value="old('responsavel_tecnico') ?? null" name="responsavel_tecnico" label="Responsável Técnico" class="mb-2" required/>
              @error('responsavel_tecnico') <div class="text-warning">{{ $message }}</div> @enderror

              <div class="row mb-2">
                <div class="col-12 col-sm-6">
                  <x-forms.input-field :value="old('telefone') ?? null" name="telefone" label="Telefone" class="telefone"/>
                  @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <x-forms.input-field :value="old('email') ?? null" name="email" type="email" label="E-mail"/>
                  @error('email') <div class="text-warning">{{ $message }}</div> @enderror
                </div>
              </div>

              {{-- Endereço do laboratório --}}
              <div class="row">
                <div class="col-8 col-sm-6">
                  <x-forms.input-field name="cep" label="CEP" class="cep"
                  :value="old('cep') ?? null" required />
                  @error('cep') <div class="text-warning">{{ $message }}</div> @enderror
                </div>
                <div class="col-4">
                  <label for="uf" class="form-label mb-0">Estado<span class="text-danger-emphasis"> * </span></label>
                  <input type="text" class="form-control" name="uf" id="uf" placeholder="UF"
                    value="{{ old('uf') ?? null }}" maxlength="2" pattern="[A-Z]{2}" 
                    title="Duas letras maiúsculo" required
                    oninput="this.value = this.value.toUpperCase()">
                    @error('uf') <div class="text-warning">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="mt-2">
                <x-forms.input-field name="endereco" label="Endereço" placeholder="Ex. Av. Brasil, 1234"
                :value="old('endereco') ?? null" required />
                @error('endereco') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
      
              <div class="row mt-2">
                <div class="col-12 col-sm-6">
                  <x-forms.input-field name="complemento" label="Complemento" placeholder="Ex. Sala 101"
                  :value="old('complemento') ?? null" />
                  @error('complemento') <div class="text-warning">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <x-forms.input-field name="bairro" label="Bairro"
                  :value="old('bairro') ?? null" />
                  @error('bairro') <div class="text-warning">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="mt-2">
                <x-forms.input-field name="cidade" label="Cidade"
                :value="old('cidade') ?? null" required/>
                @error('cidade') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
      
              <x-forms.input-textarea name="informacoes_inscricao" label="Informações da inscrição:"
                sublabel="Informe aqui quais rodadas, blocos ou poarametros esse laboratorio irá participar.">{{ old('informacoes_inscricao') ?? null }}
              </x-forms.input-textarea>
              @error('informacoes_inscricao') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
          </div>
          <div>
            <div class="row">
              <div class="col-md-auto">
                <button class="btn btn-success mt-2" type="submit" >ENVIAR E CADASTRAR OUTRO</button>
              </div>
              <div class="col-md-auto">
                <a class="btn btn-info mt-2" type="button" onclick="submitAndClose()">
                  ENVIAR E CONCLUIR
                </a>
              </div>
              <div class="col-md-auto">
                <a class="btn btn-danger mt-2" type="button" onclick="calncelAndClose()">
                  CANCELAR
                </a>
              </div>
            </div>
          </div>
        </form>
      @endif
    </div>
  </div>
  <form action="{{ route('limpa-sessao-interlab')}}" method="post" id="limpa-sessao-interlab">@csrf</form>
</div>

<script>
  function submitAndClose() {
    document.getElementById('encerra_cadastro').value = '1';
    document.getElementById('confirma-inscricao-interlab').submit();
  }

  function calncelAndClose(){
    document.getElementById('limpa-sessao-interlab').submit();
  }
</script>
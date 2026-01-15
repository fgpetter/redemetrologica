<div class="row">
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

        @if($pessoa->empresas->count() > 0 )
  
          <form action="{{ route('confirma-inscricao-interlab') }}" method="post" id="confirma-inscricao-interlab" >
            @csrf
            <input type="hidden" name="encerra_cadastro" value="0" id="encerra_cadastro">
            <input type="hidden" name="interlab_uid" value="{{ $interlab->uid }}">
            <div class="card border overflow-hidden card-border-dark shadow-none">
              <div class="card-header">
                <h6 class="card-title mb-0">Informe os dados do Laboratório para envio de amostras:</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-12 col-xl-6">
                    <x-forms.input-field :value="old('laboratorio') ?? null" name="laboratorio" label="Laboratório" class="mb-2" required/>
                    @error('laboratorio') <div class="text-warning">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12 col-xl-6">
                    <x-forms.input-field :value="old('responsavel_tecnico') ?? null" name="responsavel_tecnico" label="Responsável Técnico" class="mb-2" required/>
                    @error('responsavel_tecnico') <div class="text-warning">{{ $message }}</div> @enderror
                  </div>
                </div>
  
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
                <div class="row my-3 gy-2">
                  <x-painel.enderecos.form-endereco :nome="false" :padrao="false" />
                </div>
  
                <x-forms.input-textarea name="informacoes_inscricao" label="Informações da inscrição:"
                  sublabel="Informe aqui quais rodadas, blocos ou poarametros esse laboratorio irá participar.">{{ old('informacoes_inscricao') ?? null }}
                </x-forms.input-textarea>
                @error('informacoes_inscricao') <div class="text-warning">{{ $message }}</div> @enderror
  
                @if($pessoa->empresas->count() > 1 )
                  <div class="mt-4">
                    <x-forms.input-select name="empresa_uid" label="Selecione empresa para nota fiscal e cobrança:" required="true">
                      <option value=""> Selecione </option>
                        @foreach ($pessoa->empresas as $empresa)
                          <option value="{{ $empresa->uid }}"> {{ $empresa->cpf_cnpj .' - '.$empresa->nome_razao }} </option>
                        @endforeach
                      </x-forms.input-select>
                  </div>
                @elseif($pessoa->empresas->count() == 1 )
                  <input type="hidden" name="empresa_uid" value="{{ $pessoa->empresas->first()->uid }}" >
                @endif
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

        @else  {{-- FORM PARA ADICIONAR CNPJ --}}
          <form action="{{ route('informa-empresa-interlab') }}" method="post" class="mb-5">
            @csrf
              <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-warning rounded">
                <i class="ri-information-fill text-warning fs-5"></i> Atencão!:
                <p class="mb-2 text-black">
                  Você precisa informar o CNPJ da empresa para continuar. <br>
                  <small>
                    Os dados da empresa serão usados para <strong>emissão da Nota Fiscal e cobrança</strong>. <br>
                  </small>
                </p>
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
      </div>
    </div>
    <form action="{{ route('limpa-sessao-interlab')}}" method="post" id="limpa-sessao-interlab">@csrf</form>
  
  </div>
  <div class="col-12 col-xxl-5 col-xl-4">
    <div class="card">
      <div class="card-body">
        @if($pessoa->empresas->count() > 0 )
          <div class="mx-3">
            <h5 class="card-subtitle mb-2 text-primary-emphasis">Empresas relacionadas ao seu usuário:</h5>
            @foreach ($pessoa->empresas as $empresa)
              <p>
                <strong>Razaão social:</strong> {{ $empresa->nome_razao }} <br>
                <strong>CNPJ:</strong> {{ $empresa->cpf_cnpj }} <br>
              </p>
            @endforeach
            <form action="{{ route('informa-empresa-interlab') }}" method="post" class="mb-5">
              @csrf
              <div class="row">
                <div class="col-9">
                  <input type="text" class="form-control" name="cnpj" placeholder="CNPJ" id="input-cnpj">
                  @error('cnpj')<div class="text-warning">{{ $message }}</div>@enderror
                </div>
                <div class="col-3">
                  <button type="submit" class="btn btn-primary">Adicionar</button>
                </div>
              </div>
            </form>
  
          </div>
    
          @if( $inscritos->count() > 0 )
            <div class="card bg-light shadow-none">
              <h6 class="card-title mb-0 px-3 pt-2 pb-0">Laboratórios inscritos nesse interlab:</h6>
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
        @endif
      </div>
    </div>

  </div>

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
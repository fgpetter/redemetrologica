@if( $inscrito && $empresa && !$convidado)

  <div class="alert alert-success alert-dismissible bg-body-secondary fade show text-dark" role="alert">
    <strong>Tudo certo!</strong> <br>
    <p><strong>Você já está inscrito</strong> no curso {{ $curso->curso->descricao }}. <br>
      Se você não vai enviar nenhum convite para outras pessoas, clique no botão para concluir a inscrição.
    </p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

@elseif ( $inscrito && (!$empresa || $convidado) )

  <div class="alert alert-success alert-dismissible bg-body-secondary fade show text-dark" role="alert">
    <strong>Tudo certo!</strong> <br>
    <p><strong>Você já está inscrito</strong> no curso {{ $curso->curso->descricao }}.</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  
@else

@if( $empresa )
  <div class="alert alert-secondary alert-dismissible bg-body-secondary fade show" role="alert">
    <strong>IMPORTANTE: </strong> <br>
    <p>Para confirmar sua inscrição <strong>individual</strong>, preencha os campos abaixo e clique em confirmar".</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<form action="{{ route('confirma-inscricao') }}" method="post">
  @csrf
  <div class="row">
    <div class="col-5"> {{-- Dados principais --}}
      <h6 class="card-subtitle mb-2 text-primary-emphasis">Dados do participante:</h6>
      <label for="nome" class="form-label mt-3 mb-0">Nome <span class="text-danger"> * </span></label>
      <input type="text" class="form-control" name="nome" value="{{ $pessoa->nome_razao }}" required>
      @error('nome')<div class="text-warning">{{ $message }}</div>@enderror

      <label for="email" class="form-label mb-0 mt-2">Email <span class="text-danger"> * </span></label>
      <input type="email" class="form-control" name="email" value="{{ $pessoa->email }}" required>
      @error('email')<div class="text-warning">{{ $message }}</div>@enderror

      <label for="telefone" class="form-label mb-0 mt-2">Telefone <span class="text-danger"> * </span></label>
      <input type="text" class="form-control telefone" name="telefone" value="{{ $pessoa->telefone }}" required>
      @error('telefone')<div class="text-warning">{{ $message }}</div>@enderror

      <label for="cpf_cnpj" class="form-label mb-0 mt-2">CPF <span class="text-danger"> * </span></label>
      <input type="text" class="form-control" name="cpf_cnpj" id="input-cpf" value="{{ $pessoa->cpf_cnpj }}" required>
      @error('cpf_cnpj')<div class="text-warning">{{ $message }}</div>@enderror
    </div>

    @if(!$empresa)
      <div class="col-7"> {{-- Endereço pessoal --}}
        <h6 class="card-subtitle mb-2 text-primary-emphasis">Endereço para emissão da Nota Fiscal:</h6>
        <input type="hidden" name="id_endereco" value="{{ $pessoa->enderecos->first()->uid ?? '' }}">

        <div class="row mt-3 gy-2">
          <x-painel.enderecos.form-endereco :nome="false" :padrao="false" />
        </div>

      </div>
    @endif
  </div>

  <input type="hidden" name="id_pessoa" value="{{ $pessoa->id }}">
  <input type="hidden" name="id_curso" value="{{ $curso->id }}">
  @if($empresa)<input type="hidden" name="id_empresa" value="{{ $empresa->id }}">@endif
  @if($convidado)<input type="hidden" name="convidado" value="1">@endif

  <button class="btn btn-primary mt-3 btn-cadastro">Confirmar meu cadastro</button>
</form>

@endif
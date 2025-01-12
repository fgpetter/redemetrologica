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
    <div class="col-6"> {{-- Dados principais --}}
      <h6 class="card-subtitle mb-2 text-primary-emphasis">Dados do participante:</h6>
      <label for="nome" class="form-label">Nome <span class="text-danger"> * </span></label>
      <input type="text" class="form-control" name="nome" value="{{ $pessoa->nome_razao }}" required>
      @error('nome')<div class="text-warning">{{ $message }}</div>@enderror

      <label for="email" class="form-label mt-2">Email <span class="text-danger"> * </span></label>
      <input type="email" class="form-control" name="email" value="{{ $pessoa->email }}" required>
      @error('email')<div class="text-warning">{{ $message }}</div>@enderror

      <label for="telefone" class="form-label mt-2">Telefone <span class="text-danger"> * </span></label>
      <input type="text" class="form-control telefone" name="telefone" value="{{ $pessoa->telefone }}" required>
      @error('telefone')<div class="text-warning">{{ $message }}</div>@enderror

      <label for="cpf_cnpj" class="form-label mt-2">CPF <span class="text-danger"> * </span></label>
      <input type="text" class="form-control" name="cpf_cnpj" id="input-cpf" value="{{ $pessoa->cpf_cnpj }}" required>
      @error('cpf_cnpj')<div class="text-warning">{{ $message }}</div>@enderror
    </div>

    @if(!$empresa)
      <div class="col-6"> {{-- Endereço pessoal --}}
        <h6 class="card-subtitle mb-2 text-primary-emphasis">Insira seus dados de endereço:</h6>
        <input type="hidden" name="id_endereco" value="{{ $pessoa->enderecos->first()->uid ?? '' }}">

        <div class="row">
          <div class="col-8">
            <x-forms.input-field name="cep" label="CEP" class="input-cep"
            :value="old('cep') ?? $pessoa->enderecos->first()->cep ?? null" required />
            @error('cep') <div class="text-warning">{{ $message }}</div> @enderror
          </div>
          <div class="col-4">
            <label for="uf" class="form-label">Estado<span class="text-danger-emphasis"> * </span></label>
            <input type="text" class="form-control" name="uf" id="uf" 
              value="{{ old('uf') ?? $endereco->uf ?? null }}" maxlength="2" pattern="[A-Z]{2}" 
              title="Duas letras maiúsculo" required
              oninput="this.value = this.value.toUpperCase()">
              @error('uf') <div class="text-warning">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="mt-2">
          <x-forms.input-field name="endereco" label="Endereço" placeholder="Ex. Av. Brasil, 1234"
          :value="old('endereco') ?? $pessoa->enderecos->first()->endereco ?? null" required />
          @error('endereco') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="mt-2">
          <x-forms.input-field name="complemento" label="Complemento" placeholder="Ex. Sala 101"
          :value="old('complemento') ?? $pessoa->enderecos->first()->complemento ?? null" />
          @error('complemento') <div class="text-warning">{{ $message }}</div> @enderror
          </div>

          <div class="mt-2">
          <x-forms.input-field name="bairro" label="Bairro"
          :value="old('bairro') ?? $pessoa->enderecos->first()->bairro ?? null" />
          @error('bairro') <div class="text-warning">{{ $message }}</div> @enderror
          </div>

          <div class="mt-2">
          <x-forms.input-field name="cidade" label="Cidade"
          :value="old('cidade') ?? $pessoa->enderecos->first()->cidade ?? null" />
          @error('cidade') <div class="text-warning">{{ $message }}</div> @enderror
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
<form method="POST"
    action="{{  route('avaliador-enderecos-update', $avaliador->uid)  }}">
    @csrf
    {{-- enderecos Pessoal --}}
    <div class="row mt-3 py-2 ">
        <div class="col-12">
            <h5 class="mb-1">Endereço Pessoal</h5>
        </div>

        <div class="col-5 col-sm-4">
            <x-forms.input-field :value="old('pessoal_cep') ?? ($enderecopessoal->cep ?? '')" name="pessoal_cep" label="CEP" class="cep" />
            @error('pessoal_cep')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-8">
            <x-forms.input-field :value="old('pessoal_endereco') ?? ($enderecopessoal->endereco ?? '')" name="pessoal_endereco" label="Endereço com número"
                placeholder="Ex. Av. Brasil, 1234" />
            @error('pessoal_endereco')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-6">
            <x-forms.input-field :value="old('pessoal_complemento') ?? ($enderecopessoal->complemento ?? '')" name="pessoal_complemento" label="Complemento"
                placeholder="Ex. Sala 101" />
            @error('pessoal_complemento')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-6">
            <x-forms.input-field :value="old('pessoal_bairro') ?? ($enderecopessoal->bairro ?? '')" name="pessoal_bairro" label="Bairro" />
            @error('pessoal_bairro')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-6">
            <x-forms.input-field :value="old('pessoal_cidade') ?? ($enderecopessoal->cidade ?? '')" name="pessoal_cidade" label="Cidade" />
            @error('pessoal_cidade')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-4 col-sm-2">
            <label for="pessoal_uf" class="form-label mb-0">UF</label>
            <input type="text" class="form-control" name="pessoal_uf" id="pessoal_uf"
                value="{{ old('pessoal_uf') ?? ($enderecopessoal->uf ?? null) }}" maxlength="2" pattern="[A-Z]{2}"
                title="Duas letras maiúsculo" oninput="this.value = this.value.toUpperCase()">
            @error('pessoal_uf')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>
    </div>
    {{-- enderecos Pessoal --}}
 
    {{-- enderecos comercial --}}
    <div class="row py-2 mb-2 border-top"> 
        <div class="col-12">
            <h5 class="mb-1">Endereço Comercial</h5>
        </div>

        <div class="col-5 col-sm-4">
            <x-forms.input-field :value="old('comercial_cep') ?? ($enderecocomercial->cep ?? '')" name="comercial_cep" label="CEP" class="cep" />
            @error('comercial_cep')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-8">
            <x-forms.input-field :value="old('comercial_endereco') ?? ($enderecocomercial->endereco ?? '')" name="comercial_endereco" label="Endereço com número"
                placeholder="Ex. Av. Brasil, 1234" />
            @error('comercial_endereco')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-6">
            <x-forms.input-field :value="old('comercial_complemento') ?? ($enderecocomercial->complemento ?? '')" name="comercial_complemento" label="Complemento"
                placeholder="Ex. Sala 101" />
            @error('comercial_complemento')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-6">
            <x-forms.input-field :value="old('comercial_bairro') ?? ($enderecocomercial->bairro ?? '')" name="comercial_bairro" label="Bairro" />
            @error('comercial_bairro')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-6">
            <x-forms.input-field :value="old('comercial_cidade') ?? ($enderecocomercial->cidade ?? '')" name="comercial_cidade" label="Cidade" />
            @error('comercial_cidade')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-4 col-sm-2">
            <label for="comercial_uf" class="form-label mb-0">UF</label>
            <input type="text" class="form-control" name="comercial_uf" id="comercial_uf"
                value="{{ old('comercial_uf') ?? ($enderecocomercial->uf ?? null) }}" maxlength="2" pattern="[A-Z]{2}"
                title="Duas letras maiúsculo" oninput="this.value = this.value.toUpperCase()">
            @error('comercial_uf')
                <div class="text-warning">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-4">{{ $avaliador->id ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
    {{-- enderecos comercial --}}
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
      function setupCepLookup(type) {
        var addressFields = $(
          `input[name="${type}_endereco"], input[name="${type}_bairro"], input[name="${type}_cidade"], input[name="${type}_uf"], input[name="${type}_complemento"]`
        );

        $(`input[name="${type}_cep"]`).keyup(function() {
          const cep = $(this).val().replace(/\D/g, '');

          if (cep.length == 8) {
            addressFields.prop('disabled', true);
            $.ajax({
              url: '/painel/endereco/check',
              type: "GET",
              dataType: 'json',
              data: {
                cep: cep
              },
              success: function(response) {

                if (response.error) {
                  $.ajax({
                    url: "https://viacep.com.br/ws/" + cep + "/json/",
                    success: function(viacepResponse) {
                      $(`input[name="${type}_endereco"]`).val(
                        viacepResponse.logradouro);
                      $(`input[name="${type}_bairro"]`).val(
                        viacepResponse.bairro);
                      $(`input[name="${type}_cidade"]`).val(
                        viacepResponse.localidade);
                      $(`input[name=${type}_uf]`).val(
                        viacepResponse.uf);
                      addressFields.prop('disabled', false);
                    }
                  });

                } else {
                  $(`input[name="${type}_endereco"]`).val(response
                    .endereco);
                  $(`input[name="${type}_bairro"]`).val(response.bairro);
                  $(`input[name="${type}_cidade"]`).val(response.cidade);
                  $(`input[name="${type}_uf"]`).val(response.uf);
                  addressFields.prop('disabled', false);
                }
              },
              error: function(data) {
                console.log('error!', data);
                addressFields.prop('disabled', false);
              }
            });
          }
        });
      }

      setupCepLookup('pessoal');
      setupCepLookup('comercial');
    });
</script>
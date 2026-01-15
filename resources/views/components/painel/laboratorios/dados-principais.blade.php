<form method="POST" action="{{route('laboratorio-update', $laboratorio->uid)}}">
  @csrf
    <div class="row gy-3">

      <div class="col-6">
        <x-forms.input-field name="nome_razao" :value="$laboratorio->pessoa->nome_razao ?? null" label="Razão Social"  :readonly=true 
          tooltip="Para alterar o NOME ou CNPJ, clique em EDITAR EMPRESA."/>
      </div>

      <div class="col-4">
        <x-forms.input-field name="cpf_cnpj" :value="$laboratorio->pessoa->cpf_cnpj ?? null" label="CNPJ"  :readonly=true />
      </div>
      <div class="col-2 d-flex align-items-center justify-content-center" style="white-space: nowrap;">
        <a href="{{ route('pessoa-insert', $laboratorio->pessoa->uid) }}" class="link-primary fw-medium">
          Editar Empresa
          <i class="ri-arrow-right-line align-middle"></i>
        </a>
      </div>

      <div class="col-10">
        <x-forms.input-field name="nome_laboratorio" 
          :value="old('nome_laboratorio') ?? ($laboratorio->nome_laboratorio ?? null)" 
          label="Nome do laboratorio"
          tooltip="Nome desse laboratorio que irá aparecer no site e no cadastro de avaliação."
          required/>
      </div>

      <div class="col-2">
        <x-forms.input-field name="associado" :value="$laboratorio->pessoa->associado ? 'SIM' : 'NÃO'" label="Associado" readonly />
      </div>

      <div class="col-4">
        <x-forms.input-field name="telefone" 
          :value="old('telefone') ?? ($laboratorio->telefone ?? $laboratorio->pessoa->telefone ?? null)" 
          label="Telefone" mask="telefone"/>
          @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-4">
        <x-forms.input-field type="email" name="email" 
          :value="old('email') ?? ($laboratorio->email ?? $laboratorio->pessoa->email ?? null)" 
          label="E-mail" />
          @error('email') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-4">
        <x-forms.input-field name="contato" :value="old('contato') ?? ($laboratorio->contato ?? null)" label="Pessoa de contato" />
        @error('contato') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-4">
        <x-forms.input-field name="responsavel_tecnico" :value="old('responsavel_tecnico') ?? ($laboratorio->responsavel_tecnico ?? null)" label="Responsável Técnico" />
        @error('responsavel_tecnico') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-4">
        <x-forms.input-field name="cod_laboratorio" :value="old('cod_laboratorio') ?? ($laboratorio->cod_laboratorio ?? null)" label="Código do Laboratório" />
        @error('cod_laboratorio') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
      </div>

    </div>
</form>
@if ($laboratorio->uid)
  <div class="col-12">
    <x-painel.laboratorios.form-delete route="laboratorio-delete" id="{{ $laboratorio->uid }}" />
  </div>
@endif

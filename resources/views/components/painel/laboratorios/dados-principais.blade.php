<form method="POST" action="{{route('laboratorio-update', $laboratorio->uid)}}">
  @csrf
    <div class="row gy-3">

      <div class="col-8">
        <x-forms.input-field name="nome_razao" :value="$laboratorio->pessoa->nome_razao ?? null" label="Razão Social"  :readonly=true />
      </div>

      <div class="col-4">
        <x-forms.input-field name="cpf_cnpj" :value="$laboratorio->pessoa->cpf_cnpj ?? null" label="CNPJ"  :readonly=true />
      </div>

      <div class="col-10">
        <x-forms.input-field name="nome_laboratorio" 
          :value="old('nome_laboratorio') ?? ($laboratorio->nome_laboratorio ?? null)" 
          label="Nome do laboratorio"
          tooltip="Nome desse laboratorio que irá aparecer no site e no cadastro de avaliação."
          required/>
      </div>

      <div class="col-2">
        <x-forms.input-select name="laboratorio_associado" label="Asociado">
          <option value="0">NÃO</option>
          <option @selected($laboratorio->laboratorio_associado == 1 || $laboratorio->pessoa->associado == 1 ) value="1">SIM</option>
        </x-forms.input-select>
      </div>

      <div class="col-4">
        <x-forms.input-field name="telefone" :value="old('telefone') ?? ($laboratorio->telefone ?? $laboratorio->pessoa->telefone ?? null)" label="Telefone" class="telefone"/>
      </div>

      <div class="col-4">
        <x-forms.input-field type="email" name="email" :value="old('email') ?? ($laboratorio->email ?? $laboratorio->pessoa->email ?? null)" label="E-mail" />
      </div>

      <div class="col-4">
        <x-forms.input-field name="contato" :value="old('contato') ?? ($laboratorio->contato ?? null)" label="Pessoa de contato" />
      </div>

      <div class="col-4">
        <x-forms.input-field name="responsavel_tecnico" :value="old('responsavel_tecnico') ?? ($laboratorio->responsavel_tecnico ?? null)" label="Responsável Técnico" />
      </div>

      <div class="col-4">
        <x-forms.input-field name="cod_laboratorio" :value="old('cod_laboratorio') ?? ($laboratorio->cod_laboratorio ?? null)" label="Código do Laboratório" />
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
      </div>

    </div>
</form>

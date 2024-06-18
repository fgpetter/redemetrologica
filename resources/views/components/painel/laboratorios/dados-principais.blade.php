<form method="POST" action="{{route('laboratorio-update', $laboratorio->uid)}}">
  @csrf
    <div class="row gy-3">

      <div class="col-6">
        <x-forms.input-field name="nome_razao" :value="$laboratorio->pessoa->nome_razao ?? null" label="Razão Social"  :readonly=true />
      </div>

      <div class="col-6">
        <x-forms.input-field name="cpf_cnpj" :value="$laboratorio->pessoa->cpf_cnpj ?? null" label="CNPJ"  :readonly=true />
      </div>

      <div class="col-10">
        <x-forms.input-field name="nome" :value="old('nome') ?? ($laboratorio->nome ?? null)" label="Nome do laboratorio" />
      </div>

      <div class="col-2">
        <x-forms.input-select name="laboratorio_associado" label="Asociado">
          <option @selected($laboratorio->laboratorio_associado == 1) value="1">SIM</option>
          <option @selected($laboratorio->laboratorio_associado == 0) value="0">NÃO</option>
        </x-forms.input-select>
      </div>

      <div class="col-6">
        <x-forms.input-field name="telefone" :value="old('telefone') ?? ($laboratorio->telefone ?? null)" label="Telefone" />
      </div>

      <div class="col-6">
        <x-forms.input-field name="email" :value="old('email') ?? ($laboratorio->email ?? null)" label="E-mail" />
      </div>

      <div class="col-6">
        <x-forms.input-field name="responsavel_tecnico" :value="old('responsavel_tecnico') ?? ($laboratorio->responsavel_tecnico ?? null)" label="Responsável Técnico" />
      </div>

      <div class="col-6">
        <x-forms.input-field name="cod_laboratorio" :value="old('cod_laboratorio') ?? ($laboratorio->cod_laboratorio ?? null)" label="Código do Laboratório" />
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
      </div>

    </div>
</form>

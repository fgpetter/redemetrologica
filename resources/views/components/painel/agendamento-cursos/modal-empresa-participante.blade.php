{{-- modal --}}
<div class="modal fade" id="{{ isset($avaliacao) ? 'participanteModal'.$avaliacao->uid : 'participanteModal'}}" tabindex="-1" aria-labelledby="participanteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="participanteModalLabel">Adicionar Participante</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
      <div class="row gy-3">
        <form method="POST" action="{{-- isset($avaliacao) ? route('avaliador-create-avaliacao', $avaliador->uid) : route('avaliador-update-avaliacao', $avaliacao->uid) --}}">
          @csrf
          <div class="col-sm-12">
            <x-forms.input-field :value="old('razao_social') ?? ($agendamento_curso->razao_social ?? null)" name="razao_social" label="Razao Social"
              placeholder="" />
            @error('razao_social')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-sm-12">
            <x-forms.input-field :value="old('endereco') ?? ($agendamento_curso->endereco ?? null)" name="endereco" label="Endereco"
              placeholder="" />
            @error('endereco')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('cep') ?? ($agendamento_curso->cep ?? null)" name="cep" label="CEP" placeholder="" />
            @error('cep')
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
          <div class="col-sm-4">
            <x-forms.input-field :value="old('UF') ?? ($agendamento_curso->UF ?? null)" name="UF" label="UF" placeholder="" />
            @error('UF')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('IE') ?? ($agendamento_curso->IE ?? null)" name="IE" label="Inscrição Estadual"
              placeholder="" />
            @error('IE')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('IM') ?? ($agendamento_curso->IM ?? null)" name="IM" label="Inscrição Municipal"
              placeholder="" />
            @error('IM')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-sm-12">
            <x-forms.input-field :value="old('Email') ?? ($agendamento_curso->Email ?? null)" name="Email" label="Email" placeholder="" />
            @error('Email')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="col-sm-6">
            <x-forms.input-field :value="old('ficou_sabendo') ?? ($agendamento_curso->ficou_sabendo ?? null)" name="ficou_sabendo"
              label="Como Ficou Sabendo do Curso?" placeholder="" />
            @error('ficou_sabendo')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-sm-6">
            <x-forms.input-field :value="old('resp_info') ?? ($agendamento_curso->resp_info ?? null)" name="resp_info"
              label="Responsável pelas Informações" placeholder="" />
            @error('resp_info')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="col-sm-6">
            <x-forms.input-select name="associado" label="Associado">
          
              <option value="SIM">SIM</option>
              <option value="NÃO">NÃO</option>
          
          
            </x-forms.input-select>
            @error('associado')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-sm-6">
            <x-forms.input-select name="emite_NF" label="Emite NF">
          
              <option value="SIM">SIM</option>
              <option value="NÃO">NÃO</option>
          
          
            </x-forms.input-select>
            @error('emite_NF')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          </div>
        </form>

      </div>

    </div>
    </div>
  </div>
  </div>
  {{-- endmodal --}}
  
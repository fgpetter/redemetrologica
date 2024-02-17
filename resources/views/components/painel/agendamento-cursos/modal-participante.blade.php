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
            <x-forms.input-field :value="old('nome') ?? ($agendamento_curso->nome ?? null)" name="nome" label="Nome" placeholder="" />
            @error('nome')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-8">
            <x-forms.input-field :value="old('cargo') ?? ($agendamento_curso->cargo ?? null)" name="cargo" label="Cargo" placeholder="" />
            @error('cargo')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('fone') ?? ($agendamento_curso->fone ?? null)" name="fone" label="Fone" placeholder="" />
            @error('fone')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-8">
            <x-forms.input-field :value="old('email') ?? ($agendamento_curso->email ?? null)" name="email" label="Email" placeholder="" />
            @error('email')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('valor') ?? ($agendamento_curso->valor ?? null)" name="valor" label="Valor" placeholder="" />
            @error('valor')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-select name="confirmou" label="confirmou">
              <option value="SIM">SIM</option>
              <option value="NÃO">NÃO</option>
            </x-forms.input-select>
            @error('confirmou')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('data_confirm') ?? ($agendamento_curso->data_confirm ?? null)" type="date" name="data_confirm"
              label="Usuário/Data Confirmação" />
            @error('data_confirm')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('data_certificado') ?? ($agendamento_curso->data_certificado ?? null)" type="date" name="data_certificado"
              label="Certificado Enviado Em" />
            @error('data_certificado')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-select name="pesquisa" label="Respondeu Pesquisa">
              <option value="SIM">SIM</option>
              <option value="NÃO">NÃO</option>
            </x-forms.input-select>
            @error('pesquisa')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
        </form>

      </div>

    </div>
    </div>
  </div>
  </div>
  {{-- endmodal --}}
  
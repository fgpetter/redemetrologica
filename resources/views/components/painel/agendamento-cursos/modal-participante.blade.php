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
            <x-forms.input-field :value="old('nome') ?? ($agendamento_curso->nome ?? null)" name="nome" label="Nome" />
            @error('nome')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('fone') ?? ($agendamento_curso->fone ?? null)" name="fone" label="Telefone" class="telefone"/>
            @error('fone')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('email') ?? ($agendamento_curso->email ?? null)" name="email" label="Email" type="email" />
            @error('email')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('valor') ?? ($agendamento_curso->valor ?? null)" name="valor" label="Valor" />
            @error('valor')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('data_confirmacao') ?? ($agendamento_curso->data_confirmacao ?? null)" type="date" name="data_confirmacao"
              label="Usuário/Data Confirmação" />
            @error('data_confirmacao')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('certificado_emitido') ?? ($agendamento_curso->certificado_emitido ?? null)" type="date" name="certificado_emitido"
              label="Certificado Enviado Em" />
            @error('certificado_emitido')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-4">
            <x-forms.input-field :value="old('resposta_pesquisa') ?? ($agendamento_curso->resposta_pesquisa ?? null)" type="date" name="resposta_pesquisa"
              label="Pesqisa Respondida Em" />
            @error('resposta_pesquisa')<div class="text-warning">{{ $message }}</div>@enderror
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>

      </div>

    </div>
    </div>
  </div>
  </div>
  {{-- endmodal --}}
  
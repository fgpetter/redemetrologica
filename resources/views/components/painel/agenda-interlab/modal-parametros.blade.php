<div class="modal fade" id="modal_parametro_cadastro" 
  tabindex="-1" aria-labelledby="modalgridParametro" aria-modal="true">
  <div class="modal-dialog modal-dialog-right modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar Parametro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('salvar-parametro') }}" method="POST">
          @csrf
          <input type="hidden" name="agenda_interlab_id" value="{{ $agendainterlab->id }}">
          <div class="row gy-3">
            <div class="col-12">
              <x-forms.input-select name="parametro_id" label="Parametro" required>
                <option>- Selecione</option>
                @foreach ($parametros as $parametro)
                  <option value="{{ $parametro->id }}">{{ $parametro->descricao }}</option>
                @endforeach
              </x-forms.input-select>
              @error('parametro_id')<span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
            </div>
          </div>
          <div class="modal-footer my-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
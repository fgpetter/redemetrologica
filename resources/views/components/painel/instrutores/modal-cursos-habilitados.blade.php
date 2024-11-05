@props([
  "cursos" => null,
  "curso" => null,
  "instrutor" => null,
  "cursohabilitado" => null
])

<div class="modal fade" id="{{ isset($cursohabilitado) ? 'cursoshabilitadosModal'.$cursohabilitado->uid : 'cursoshabilitadosModal'}}" 
  tabindex="-1" aria-labelledby="cursoshabilitadosModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cursoshabilitadosModalLabel">
          {{ isset($cursohabilitado) ? 'Editar Curso' : "Adicionar Curso"}}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" 
          action="{{ isset($cursohabilitado) ? route('instrutor-update-curso-habilitado', $cursohabilitado->uid) : route('instrutor-create-curso-habilitado', $instrutor->uid) }}">
          @csrf
          <div class="row gy-3">

            @if($cursos)
              <div class="col-sm-12">
                <x-forms.input-select name="curso" label="Curso">
                  <option value="">Selecione um curso</option>
                  @foreach ($cursos as $curso)
                    <option value="{{ $curso->id }}">{{ $curso->descricao }}</option>
                  @endforeach
                </x-forms.input-select>
                @error('curso') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
            @elseif($curso)
              <h6 class="pe-5">{{ $curso->descricao }}</h6>
            @endif

            <div class="col-sm-6">
              <x-forms.input-select name="habilitado" label="Habilitado">
                <option value="0">NÃO </option>
                <option @selected($cursohabilitado->habilitado ?? null) value="1">SIM</option>
              </x-forms.input-select>
              @error('habilitado') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-sm-6"></div>

            <div class="col-sm-6">
              <x-forms.input-select name="conhecimento" label="Conhecimento <br> <small>(Graduação na área de atuação do curso)</small>">
                <option value="0 ">NÃO </option>
                <option @selected($cursohabilitado->conhecimento ?? null) value="1 ">SIM</option>

              </x-forms.input-select>
              @error('conhecimento') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-6">
              <x-forms.input-select name="experiencia" label="Experiência <br> <small>(Mínimo de 02 anos na área de atuação do curso)</small>"> 
                <option value="0 ">NÃO </option> 
                <option @selected($cursohabilitado->experiencia ?? null) value="1 ">SIM</option>
              </x-forms.input-select>
              @error('experiencia') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-12">
              <x-forms.input-textarea name="analise_observacoes" label="Análise/Observações"
                >{{ old('analise_observacoes') ?? ($cursohabilitado->observacoes ?? null) }}
              </x-forms.input-textarea>
              @error('analise_observacoes') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

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
{{-- endmodal --}}

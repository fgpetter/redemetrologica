@php
  $pesquisa = $avaliacao->pesquisa;
  $perguntas = [
    1 => 'Postura dos avaliadores',
    2 => 'Clareza das informações contidas no relatório de avaliação',
    3 => 'Pontualidade dos avaliadores',
    4 => 'Avaliadores foram fiéis aos critérios de avaliação previamente estabelecidos pela Rede Metrológica',
    5 => 'Clareza das informações colocadas na reunião de abertura e encerramento de avaliação',
    6 => 'O atendimento às expectativas do laboratório referente à avaliação realizada',
    7 => 'A contribuição da Rede Metrológica para a melhoria do Sistema da Qualidade do laboratório',
  ];
  $avaliadoresUnicos = $avaliacao->areas->unique('avaliador_id');
  $comentariosSalvos = collect($pesquisa?->comentarios_avaliadores ?? [])->keyBy('avaliador_id');
@endphp

@if (! $pesquisa)
  <p>A pesquisa será gerada automaticamente quando a Carta de Reconhecimento for marcada como SIM.</p>
@else
  <form method="POST" action="{{ route('avaliacao-pesquisa-update', $avaliacao) }}">
    @csrf

    <div class="row gy-3">
      @foreach ($perguntas as $indice => $texto)
        <div class="col-md-4">
          <x-forms.input-field
            name="nota_{{ $indice }}"
            type="number"
            :value="old('nota_'.$indice) ?? $pesquisa->{'nota_'.$indice}"
            :label="$indice.'. '.$texto"
          />
          @error('nota_'.$indice) <div class="text-warning">{{ $message }}</div> @enderror
        </div>
      @endforeach
    </div>

    <div class="row mt-3">
      <div class="col-12">
        <x-forms.input-textarea name="criterios_harmoniosos" label="Os critérios utilizados pelos avaliadores na presente avaliação e os utilizados pelos avaliadores na avaliação anterior foram harmoniosos/compatíveis? Caso negativo, favor justificar sua resposta.">
          {{ old('criterios_harmoniosos') ?? $pesquisa->criterios_harmoniosos }}
        </x-forms.input-textarea>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-12">
        <x-forms.input-textarea name="divergencias" label="Houve pontos de divergências no final da avaliação? Se “sim”, quais?">
          {{ old('divergencias') ?? $pesquisa->divergencias }}
        </x-forms.input-textarea>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-12">
        <x-forms.input-textarea name="pontos_melhoria" label="Pontos de melhoria na sistemática de avaliação / Reclamações:">
          {{ old('pontos_melhoria') ?? $pesquisa->pontos_melhoria }}
        </x-forms.input-textarea>
      </div>
    </div>

    @foreach ($avaliadoresUnicos as $area)
      <div class="row mt-3">
        <div class="col-12">
          <x-forms.input-textarea name="comentarios[{{ $area->avaliador_id }}]" :label="mb_strtoupper($area->avaliador?->pessoa?->nome_razao).':'">
            {{ old('comentarios.'.$area->avaliador_id) ?? $comentariosSalvos[$area->avaliador_id]['comentario'] ?? '' }}
          </x-forms.input-textarea>
        </div>
      </div>
    @endforeach

    <div class="row mt-3">
      <div class="col-md-4">
        <x-forms.input-field
          name="preenchido_em"
          :value="$pesquisa->preenchido_em?->format('d/m/Y H:i:s')"
          label="Data Preenchimento"
          readonly
        />
      </div>
      <div class="col-md-4">
        <x-forms.input-field
          name="responsavel"
          :value="old('responsavel') ?? $pesquisa->responsavel"
          label="Responsável Informações"
        />
      </div>
      <div class="col-md-4">
        <x-forms.input-select name="conferida" label="Pesquisa Conferida">
          <option value="0">NÃO</option>
          <option @selected(old('conferida', $pesquisa->conferida ? '1' : '0') == '1') value="1">SIM</option>
        </x-forms.input-select>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-8">
        <x-forms.input-field
          id="pesquisa-satisfacao-link"
          :value="route('pesquisa-satisfacao.show', $avaliacao)"
          label="Link da pesquisa"
          readonly
        />
      </div>
      <div class="col-md-4 d-flex align-items-end gap-2">
        <button type="button" class="btn btn-light" onclick="navigator.clipboard.writeText(document.getElementById('pesquisa-satisfacao-link').value)">
          <i class="ri-file-copy-line"></i> Copiar
        </button>
        <a href="{{ route('avaliacao-pesquisa-pdf', $avaliacao) }}" class="btn btn-light">
          <i class="ri-printer-line"></i> Imprimir
        </a>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="ri-save-line"></i> Salvar
        </button>
      </div>
    </div>
  </form>
@endif

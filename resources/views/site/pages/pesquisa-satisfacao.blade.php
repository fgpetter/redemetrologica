@extends('site.layouts.layout-site')
@section('title') Pesquisa de Satisfação @endsection
@section('content')
<div class="container my-5">
  <div class="row">
    <div class="col offset-xxl-1 col-xxl-10">
      <h1>Pesquisa de Satisfação</h1>

      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if ($estado === 'indisponivel')
        <p>Esta pesquisa de satisfação não está disponível.</p>
      @elseif ($estado === 'respondida')
        <p>Esta pesquisa de satisfação já foi respondida. Obrigado.</p>
      @else
        @php
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
        @endphp

        <p>
          Laboratório Avaliado: {{ $avaliacao->laboratorio->nome_laboratorio }} - {{ $avaliacao->laboratorio->pessoa->cpf_cnpj }}
        </p>

        <div class="table-responsive mb-3">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Avaliador</th>
                <th>Área de Atuação</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($avaliacao->areas as $area)
                <tr>
                  <td>{{ $area->avaliador?->pessoa?->nome_razao }}</td>
                  <td>{{ $area->areaAtuacao?->descricao }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <p>
          Data da Auditoria:
          {{ $avaliacao->data_inicio ? \Carbon\Carbon::parse($avaliacao->data_inicio)->format('d/m/Y') : '' }}
          a
          {{ $avaliacao->data_fim ? \Carbon\Carbon::parse($avaliacao->data_fim)->format('d/m/Y') : '' }}
        </p>

        <form method="POST" action="{{ route('pesquisa-satisfacao.submit', $avaliacao) }}">
          @csrf

          <p>Abaixo, dê uma nota de 0 a 10 para cada um dos itens:</p>

          <div class="table-responsive mb-4">
            <table class="table table-bordered text-center align-middle">
              <thead>
                <tr>
                  <th></th>
                  @for ($n = 0; $n <= 10; $n++)
                    <th>{{ $n }}</th>
                  @endfor
                </tr>
              </thead>
              <tbody>
                @foreach ($perguntas as $indice => $texto)
                  <tr>
                    <td class="text-start">{{ $indice }}. {{ $texto }}</td>
                    @for ($n = 0; $n <= 10; $n++)
                      <td>
                        <input type="radio" name="nota_{{ $indice }}" value="{{ $n }}"
                          @checked((string) old('nota_'.$indice) === (string) $n)>
                      </td>
                    @endfor
                  </tr>
                  @error('nota_'.$indice) <tr><td colspan="12" class="text-danger text-start">{{ $message }}</td></tr> @enderror
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="mb-3">
            <label class="form-label">Os critérios utilizados pelos avaliadores na presente avaliação e os utilizados pelos avaliadores na avaliação anterior foram harmoniosos/compatíveis? Caso negativo, favor justificar sua resposta.</label>
            <textarea class="form-control" name="criterios_harmoniosos" rows="3">{{ old('criterios_harmoniosos') }}</textarea>
            @error('criterios_harmoniosos') <div class="text-danger">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Houve pontos de divergências no final da avaliação? Se “sim”, quais?</label>
            <textarea class="form-control" name="divergencias" rows="3">{{ old('divergencias') }}</textarea>
            @error('divergencias') <div class="text-danger">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Pontos de melhoria na sistemática de avaliação / Reclamações:</label>
            <textarea class="form-control" name="pontos_melhoria" rows="3">{{ old('pontos_melhoria') }}</textarea>
            @error('pontos_melhoria') <div class="text-danger">{{ $message }}</div> @enderror
          </div>

          @foreach ($avaliadoresUnicos as $area)
            <div class="mb-3">
              <label class="form-label">{{ mb_strtoupper($area->avaliador?->pessoa?->nome_razao) }}:</label>
              <textarea class="form-control" name="comentarios[{{ $area->avaliador_id }}]" rows="3">{{ old('comentarios.'.$area->avaliador_id) }}</textarea>
              @error('comentarios.'.$area->avaliador_id) <div class="text-danger">{{ $message }}</div> @enderror
            </div>
          @endforeach

          <div class="mb-4">
            <label class="form-label">Responsável pelas Informações</label>
            <input type="text" class="form-control" name="responsavel" value="{{ old('responsavel') }}" maxlength="191">
            @error('responsavel') <div class="text-danger">{{ $message }}</div> @enderror
          </div>

          <div class="d-flex gap-2">
            <button type="reset" class="btn btn-light">Limpar Tudo</button>
            <button type="submit" class="btn btn-primary">Enviar Pesquisa</button>
          </div>
        </form>
      @endif
    </div>
  </div>
</div>
@endsection

<div>
  <div class="card">
    <div class="card-body">
      <div class="card border shadow-sm mb-3">
        <div class="card-body p-2">
          <div class="mb-2">
            <h6 class="card-title mb-0">Filtros</h6>
          </div>
          <div class="row gx-3 align-items-end">
            <div class="col-12 col-sm-2">
              <label class="form-label mb-0">Data Inicial</label>
              <input wire:model="dataIni" class="form-control form-control-sm" type="date">
            </div>
            <div class="col-12 col-sm-2">
              <label class="form-label mb-0">Data Final</label>
              <input wire:model="dataFim" class="form-control form-control-sm" type="date">
            </div>
            <div class="col-12 col-sm-3">
              <label class="form-label mb-0">Procurar:</label>
              <input wire:model="search" class="form-control form-control-sm" type="text">
            </div>
            <div class="col-12 col-sm-5 d-flex gap-2 mt-3 mt-sm-0 align-items-center">
              <button wire:click="$refresh" type="button" class="btn btn-sm btn-primary">
                <i class="ri-search-line"></i> Visualizar
              </button>
              <button wire:click="limpar" type="button" class="btn btn-sm btn-light text-danger">
                <i class="ri-close-line"></i> Limpar
              </button>
              <button
                type="button"
                class="btn btn-sm btn-light ms-auto"
                wire:click="imprimir"
                wire:loading.attr="disabled"
                wire:target="imprimir"
              >
                <span wire:loading.remove wire:target="imprimir">
                  <i class="ri-printer-line"></i> Imprimir
                </span>
                <span wire:loading wire:target="imprimir">
                  <i class="ri-loader-4-line"></i> Gerando PDF...
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="table-responsive" style="min-height: 25vh">
        <table class="table table-striped align-middle table-nowrap mb-0">
          <thead>
            <tr>
              <th>Data Início</th>
              <th>Laboratório</th>
              <th>Laboratório Interno</th>
              <th>Média Pesquisa</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($grupos as $mes => $itens)
              @php
                $respondidas = $itens->filter(fn ($avaliacao) => $avaliacao->pesquisa?->preenchido_em !== null && $avaliacao->med_pesquisa !== null);
                $mediaGrupo = $respondidas->isEmpty() ? '---' : formataValorBr(round($respondidas->avg('med_pesquisa'), 2));
              @endphp
              <tr>
                <td colspan="4">
                  <strong>Mês: {{ $mes }}</strong>
                  &nbsp; Média: {{ $mediaGrupo }}
                </td>
              </tr>
              @foreach ($itens as $avaliacao)
                <tr>
                  <td>{{ $avaliacao->data_inicio ? \Carbon\Carbon::parse($avaliacao->data_inicio)->format('d/m/Y') : '' }}</td>
                  <td>{{ $avaliacao->laboratorio->nome_laboratorio ?? '' }}</td>
                  <td>{{ $avaliacao->laboratorioInterno->nome ?? '' }}</td>
                  <td>
                    @if ($avaliacao->pesquisa?->preenchido_em === null)
                      Não Respondida
                    @else
                      {{ formataValorBr($avaliacao->med_pesquisa) }}
                    @endif
                  </td>
                </tr>
              @endforeach
            @empty
              <tr>
                <td colspan="4" class="text-center">Não há avaliações</td>
              </tr>
            @endforelse
          </tbody>
        </table>

        <div class="row mt-3 w-100 justify-content-between align-items-center">
          <div class="d-flex mb-3 align-items-center gap-2" style="max-width: 210px;">
            <label class="form-label mb-0 text-muted">Itens por página:</label>
            <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 70px;">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
          <div class="col">
            {{ $avaliacoes->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

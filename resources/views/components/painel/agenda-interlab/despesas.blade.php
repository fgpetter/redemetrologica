<div class="row">
  <div class="col-12 d-flex justify-content-end my-3">
    <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#despesaModal">
      <i class="ri-add-line align-bottom me-1"></i> Adicionar
    </a>
  </div>
</div>

<div class="table-responsive" style="min-height: 25vh">
  <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
    <thead>
      <tr>
        <th scope="col" style="width: 50%">Descricao</th>
        <th scope="col">Quantidade</th>
        <th scope="col">Lote</th>
        <th scope="col">Validade</th>
        <th scope="col">Val unit</th>
        <th scope="col">Val total</th>
        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
      </tr>
    </thead>
    <tbody>
        @forelse ( $interlabDespesa as $despesa)
        <tr>
          
          <td>{{ $despesa->materialPadrao->descricao }}</td>
          <td>{{ number_format($despesa->quantidade, 2, ',', '.') }}</td>
          <td>{{ $despesa->lote }}</td>
          <td>{{ $despesa->validade?->format('d/m/Y') }}</td>
          <td>{{ "R$ " . number_format($despesa->valor, 2, ',', '.') }}</td>
          <td>{{ "R$ " . number_format($despesa->total, 2, ',', '.') }}</td>
          <td>
            <div class="dropdown">
              <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                  data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
              </a>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                <li>
                  <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="{{ '#despesaModal' . $despesa->id }}">Editar</a>
                </li>
                <li>
                  <a class="dropdown-item botao-delete" href="{{ route('agenda-interlab-duplicar-despesa', $despesa->uid) }}" >Duplicar</a>
                </li>
                <li>
                  <x-painel.form-delete.delete route='delete-despesa' id="{{ $despesa->uid }}" />
                </li>
              </ul>
            </div>
          </td>
        </tr>
        <x-painel.agenda-interlab.modal-despesa 
          :despesa="$despesa" 
          :agendainterlab="$agendainterlab" 
          :materiaisPadrao="$materiaisPadrao"
          :fornecedores="$fornecedores"
          :fabricantes="$fabricantes" />
        @empty
          <tr>
            <td colspan="7" class="text-center">Não há materiais cadastrados</td>
          </tr>
        @endforelse
      
    </tbody>
  </table>

  <x-painel.agenda-interlab.modal-despesa 
    :agendainterlab="$agendainterlab" 
    :materiaisPadrao="$materiaisPadrao"
    :fornecedores="$fornecedores"
    :fabricantes="$fabricantes" />

</div>

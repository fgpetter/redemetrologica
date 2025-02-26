{{-- modal --}}
<div class="modal fade" id="enviaXLSModal" tabindex="-1" aria-labelledby="enviaXLSModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title mb-4" id="enviaXLSModalLabel">Enviar lista em Excel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row gy-3">

          <form method="POST" action="{{ route('envia-lista-inscritos', $agendacurso->uid) }}" enctype="multipart/form-data">
            @csrf
            <div class="row gy-4">
              <div class="col-12 my-1">
                <input class="form-control" name="arquivo" type="file" id="arquivo" accept=".xls, .xlsx, .csv">
                @error('arquivo')
                  <div class="text-warning">{{ $message }}</div>
                  <span class="text-muted">Alguns arquivos .xls e .xlsxx podem estar corrompidos mesmo que abram no Excell. Salve o arquivo em outra pasta ou com outro nome.</span>
                @enderror
              </div>

              <div class="col-12">
                <h6>Siga o modelo abaixo para criar sua planilha</h6>
                <table class="table table-bordered table-sm">
                  <thead>
                    <tr>
                      <th>cpf_cnpj</th>
                      <th>nome_razao</th>
                      <th>email</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td> 12345678901 </td>
                      <td> João da Silva </td>
                      <td> email@empresa.com.br </td>
                    </tr>
                    <tr>
                      <td class="text-muted" > CPF sem pontuação </td>
                      <td class="text-muted" > Nome Completo </td>
                      <td class="text-muted" > Email para contato </td>
                    </tr>
                  </tbody>
                </table>
                <span class="text-warning-emphasis" >A primeira linha da tabela dve ter os mesmos títulos da tabela de exemplo</span>
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
</div>
  {{-- endmodal --}}
  
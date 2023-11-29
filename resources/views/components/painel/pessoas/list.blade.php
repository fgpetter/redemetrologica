  <div class="card">
      <div class="card-body">
          <div class="row">
              <div class="col-12 d-flex justify-content-end mb-3">
                  <a href="{{ route('pessoa-insert') }}" class="btn btn-sm btn-success">
                      <i class="ri-add-line align-bottom me-1"></i> Adicionar
                  </a>
              </div>
          </div>

          @if (session('update-success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                  {{ session('update-success') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
          @endif

          <div class="table-responsive">
              <table class="table table-responsive table-striped align-middle table-nowrap mb-0 data-table">
                  <thead>
                      <tr>
                          <th scope="col" class="d-none d-sm-table-cell" style="width: 1%; white-space: nowrap;">ID
                          </th>
                          <th scope="col">Nome</th>
                          <th scope="col">CPF/CNPJ</th>
                          <th scope="col" class="text-wrap">Data de cadastro</th>
                          <th width="105px" scope="col">Action</th>
                      </tr>
                  </thead>
                  <tbody>

                  </tbody>
                  <tfoot>
                      <tr>
                          <th>UID</th>
                          <th>Nome Razão</th>
                          <th>CPF/CNPJ</th>
                          <th>Data de Criação</th>

                      </tr>
                  </tfoot>
              </table>
          </div>

      </div>
  </div>

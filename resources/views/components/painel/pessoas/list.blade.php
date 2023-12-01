  <div class="card">
<<<<<<< HEAD
    <div class="card-body">
      <div class="row">
        <div class="col-12 d-flex justify-content-end mb-3">
          <a href="{{ route('pessoa-insert') }}" class="btn btn-sm btn-success" > 
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

      <div class="table-responsive" style="min-height: 25vh">
        <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
          <tr>
            <th scope="col" class="d-none d-sm-table-cell" style="width: 1%; white-space: nowrap;">ID</th>
            <th scope="col">Nome</th>
            <th scope="col">CPF/CNPJ</th>
            <th scope="col" class="text-wrap">Data de cadastro</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($pessoas as $pessoa)
            <tr>
              <th scope="row" class="d-none d-sm-table-cell"><a href="{{ route('pessoa-insert', ['pessoa' => $pessoa->uid]) }}" class="fw-medium"> #{{$pessoa->uid}} </a></th>
              <td class="text-truncate" style="max-width: 50vw" >{{Str::of($pessoa->nome_razao)->limit(60)}}</td>
              <td><input type="text" class="form-control-plaintext table-cpf-cnpj" style="min-width: 135px" value="{{$pessoa->cpf_cnpj}}" readonly></td>
              <td>{{Carbon\Carbon::parse($pessoa->created_at)->format('d/m/Y')}}</td>
              <td>
                <div class="dropdown">
                  <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem" 
                      data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
=======
      <div class="card-body">
          <div class="row">
              <div class="col-12 d-flex justify-content-end mb-3">
                  <a href="{{ route('pessoa-insert') }}" class="btn btn-sm btn-success">
                      <i class="ri-add-line align-bottom me-1"></i> Adicionar
>>>>>>> BranchDoMatheus
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

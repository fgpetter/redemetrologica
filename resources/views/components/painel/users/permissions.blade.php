<div class="card">
      <div class="card-header">
        <h4 class="card-title mb-0">Permissões de usuário</h4>
      </div>
    <div class="card-body">

      @if( $errors->get('permission.*') )
        <div class="alert alert-warning alert-top-border">
          <i class="ri-alert-line me-3 align-middle fs-lg text-warning"></i>
          Permissão inválida
        </div>
      @endif

      <form method="POST" action="{{ route( 'user-permission-update', $user->id ) }}">
        @csrf
        <div class="row">
          <div class="col-12">

            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" name="permission[]" id="admin"
              @checked( in_array( 'admin', $permissions) ) >
              <label class="form-check-label" for="permission">Administrador</label>
              <small class="text-danger-emphasis text-admin d-none"><br>Usuário administrador tem acesso a todos os modulos</small>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input permission" type="checkbox" value="2" name="permission[]" id="funcionario"
              @checked( in_array( 'funcionario', $permissions) ) >
              <label class="form-check-label" for="permission">Funcionário</label>
            </div>

            <div class="form-check mt-2 mx-3">
              <input class="form-check-input permission" type="checkbox" value="3" name="permission[]" id="avaliacoes"
              @checked( in_array( 'avaliacoes', $permissions) ) >
              <label class="form-check-label" for="permission">Avaliações</label>
            </div>

            <div class="form-check mt-2 mx-3">
              <input class="form-check-input permission" type="checkbox" value="4" name="permission[]" id="cursos"
              @checked( in_array( 'cursos', $permissions) ) >
              <label class="form-check-label" for="permission">Cursos</label>
            </div>

            <div class="form-check mt-2 mx-3">
              <input class="form-check-input permission" type="checkbox" value="5" name="permission[]" id="interlabs"
              @checked( in_array( 'interlabs', $permissions) ) >
              <label class="form-check-label" for="permission">Interlabs/PEPs</label>
            </div>

            <div class="form-check mt-2 mx-3">
              <input class="form-check-input permission" type="checkbox" value="6" name="permission[]" id="financeiro"
              @checked( in_array( 'financeiro', $permissions) ) >
              <label class="form-check-label" for="permission">Financeiro</label>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input permission" type="checkbox" value="7" name="permission[]" id="cliente"
              @checked( in_array( 'cliente', $permissions) ) >
              <label class="form-check-label" for="permission">Cliente</label>
            </div>

          </div>
          
          <div class="col-12 mt-4">
            <button type="submit" class="btn btn-info px-3">Atualizar permissões</button>
          </div>
        </div>
      </form>

    </div>

</div>

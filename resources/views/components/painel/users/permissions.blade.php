<div class="card">
      <div class="card-header">
        <h4 class="card-title mb-0">Permissões de usuário</h4>
      </div>
    <div class="card-body">

      @if( $errors->get('permission.*') )
        <div class="alert alert-danger py-2">Permissão inválida</div>
      @endif

      <form method="POST" action="{{ route( 'user-permission-update', $user->id ) }}">
        @csrf
        <div class="row">
          <div class="col-12">

            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" name="permission[]" id="admin"
              @checked( $user->permissions()->where('id', 1)->exists() ) >
              <label class="form-check-label" for="permission">Administrador</label>
              <small class="text-danger-emphasis text-admin d-none"><br>Usuário administrador tem acesso a todos os modulos</small>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" value="2" name="permission[]" id="funcionario"
              @checked( $user->permissions()->where('id', 2)->exists() ) >
              <label class="form-check-label" for="permission">Funcionário</label>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input permission" type="checkbox" value="3" name="permission[]" id="avaliacoes"
              @checked( $user->permissions()->where('id', 3)->exists() ) >
              <label class="form-check-label" for="permission">Avaliações</label>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input permission" type="checkbox" value="4" name="permission[]" id="cursos"
              @checked( $user->permissions()->where('id', 4)->exists() ) >
              <label class="form-check-label" for="permission">Cursos</label>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input permission" type="checkbox" value="5" name="permission[]" id="interlabs"
              @checked( $user->permissions()->where('id', 5)->exists() ) >
              <label class="form-check-label" for="permission">Interlabs/PEPs</label>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input permission" type="checkbox" value="6" name="permission[]" id="financeiro"
              @checked( $user->permissions()->where('id', 6)->exists() ) >
              <label class="form-check-label" for="permission">Financeiro</label>
            </div>

          </div>
          
          <div class="col-12 mt-4">
            <button type="submit" class="btn btn-info px-3">Atualizar permissões</button>
          </div>
        </div>
      </form>

    </div>

</div>

<div class="card">
  <div class="card-header">
    <h4 class="card-title mb-0">Usuários</h4>
  </div><!-- end card header -->
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped align-middle table-nowrap mb-0">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Nome</th>
            <th scope="col">Perfil</th>
            <th scope="col">Permissões</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            <tr>
              <th scope="row"><a href="{{ route('user-edit', ['user' => $user['id']]) }}"
                  class="fw-medium"> #{{ $user['id'] }} </a></th>
              <td>{{ $user['name'] }}</td>
              <td>
              {{ Str::ucfirst($user->permissions->whereIn('permission', ['cliente', 'admin', 'funcionario'])->pluck('permission')->first()) }}
              </td>
              <td class="text-wrap">
              @foreach ($user->permissions->whereIn('permission', ['avaliacoes', 'cursos', 'interlabs', 'financeiro']) as $permission)
                @if($loop->index > 0) | @endif
                {{ Str::ucfirst($permission->permission) }}
              @endforeach
              </td>
              <td>
                <div class="dropdown">
                  <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                      data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                  </a>

                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    <li>
                      <a class="dropdown-item" href="{{ route('user-edit', ['user' => $user['id']]) }}">Editar</a>
                    </li>
                      <x-painel.form-delete.delete route='user-delete' id="{{ $user->id }}" />
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
            
          @empty
            <tr>
              <td colspan="5" class="text-center"> Não há usuários na base. </td>
            </tr>
          @endforelse
        </tbody>
      </table>
      <div class="row mt-3 w-100">
        {!! $users->withQueryString()->links('pagination::bootstrap-5') !!}
      </div>

    </div>

  </div>
</div>

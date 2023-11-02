<div class="col-xl-6">
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
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <th scope="row"><a href="#" class="fw-medium">#VZ2110</a></th>
                        <td>{{$user['name']}}</td>
                        <td>Usuário</td>
                        <td class="text-wrap">Painel | Editar | Incluir | Área do cliente | Usuários</td>
                        <td>
                            <a href="{{ route('user', ['id' => $user['id']]) }}" class="link-success">
                                Ver Detalhes <i class="ri-arrow-right-line align-middle"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            </table>
        </div>

        </div>
    </div>
</div>

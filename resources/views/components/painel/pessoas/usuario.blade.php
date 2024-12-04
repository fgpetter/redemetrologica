<div class="card">
    <div class="card-header d-flex align-items-center gap-3">
        <div class="avatar-sm">
            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                <i class=" ph-user-circle-plus-light"></i>
            </span>
        </div>
        <h4 class="card-title mb-0">Usuário associado</h4>
    </div><!-- end card header -->
    <div class="card-body">
      @if($pessoa->user)
        <p>
          <strong>Nome do usuário:</strong> {{ $pessoa->user->name }}
          <br>
          <strong>Email:</strong> {{ $pessoa->user->email }}
          <br><br>
          <a class="btn btn-primary" href="{{ route('user-edit', $pessoa->user->id) }}">Editar usuário</a>
        </p>
      @else
      <p> Não há usuário associado a essa pessoa</p>
        <form action="{{ route('pessoa-associa-usuario', $pessoa->uid) }}" method="post">
            @csrf
            <input type="hidden" name="nome" value="{{ $pessoa->nome_razao }}">
            <input type="hidden" name="email" value="{{ $pessoa->email }}">
            <div class="row align-items-end">
                <div class="col">
                    <button type="submit" class="btn btn-primary">Criar usuário</button>
                </div>
            </div>
        </form>
      @endif
    </div>
</div>
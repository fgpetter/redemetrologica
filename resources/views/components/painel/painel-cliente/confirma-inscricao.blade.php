<div class="col-12 col-xxl-6 col-xl-8">

  <div class="card">
    <div class="card-body">
      <h5 class="card-title mb-3">Confirme sua inscrição:</h5>
      <h6 class="card-subtitle mt-3 mb-2 text-primary-emphasis">Dados do curso:</h6>

      <p>
        <strong>Curso:</strong> {{ $curso->curso->descricao }} <br>
        <strong>Agenda:</strong> de {{ \Carbon\Carbon::parse($curso->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($curso->data_fim)->format('d/m/Y') }} <br>
        <strong>Tipo:</strong> {{ $curso->tipo_agendamento }} <br> <br>

        <strong>Objetivo:</strong> {{ $curso->curso->objetivo }} <br>  <br>

        <strong>Conteúdo programático:</strong> {{ $curso->curso->conteudo_programatico }}
      </p>
      
      @if($empresa)
        <h6 class="card-subtitle mt-3 mb-2 text-primary-emphasis">Dados da empresa contratante:</h6>

        <p>
          <strong>Razaão social:</strong> {{ $empresa->nome_razao }} <br>
          <strong>CNPJ:</strong> {{ $empresa->cpf_cnpj }} <br>
        </p>        

      @endif
      
      <form action="{{ route('confirma-inscricao') }}" method="post">
        @csrf
        @if(!$empresa)
          <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-warning rounded">
            <p class="mb-2 text-black">Essa inscrição não está relacionada a uma empresa!</p>
            <footer class="blockquote-footer mt-0 text-black">Adicione o CNPJ da sua empresa para essa inscrição ou deixe em branco para uma inscrição individual:</footer>
          </blockquote>
          <div class="col-sm-8 col-xxl-6">
            {{-- TODO Criar lógica para confirmar CNPJ via ajax --}}
            <input type="text" class="form-control" name="cnpj" placeholder="CNPJ" id="input-cnpj">
            <span id="nome-empresa"></span>
          </div>
        @endif

        <h6 class="card-subtitle mt-3 mb-2 text-primary-emphasis">Dados do participante:</h6>
        <div class="col-sm-8 col-xxl-6">
          <label for="nome" class="form-label">Nome</label>
          <input type="text" class="form-control" name="nome" value="{{ auth()->user()->pessoa->nome_razao }}">

          <label for="email" class="form-label mt-2">Email</label>
          <input type="text" class="form-control" name="email" value="{{ auth()->user()->pessoa->email }}">

          <label for="telefone" class="form-label mt-2">Telefone</label>
          <input type="text" class="form-control" name="telefone" value="{{ auth()->user()->pessoa->telefone }}">

          <label for="cpf_cnpj" class="form-label mt-2">Documento</label>
          <input type="text" class="form-control" name="cpf_cnpj" id="input-cpf" value="{{ auth()->user()->pessoa->cpf_cnpj }}">


        </div>

        @if($empresa && !$convite)
          <h6 class="card-subtitle mt-3 text-primary-emphasis">Adicionar outros participantes da minha empresa:</h6>
          <p class="pe-3">Ao completar seu cadastro, as pessoas adicionadas nessa lista receberão um email com link para confirmarem suas inscrições.</p>
          <div class="row row-invite mt-1 gx-1">
            <div class="col-5">
              <input type="text" class="form-control" name="nome[]" placeholder="Nome">
            </div>
            <div class="col-5">
              <input type="email" class="form-control" name="email[]" placeholder="Email">
            </div>
            <div class="col-2">            
              <a href="javascript:void(0)" onclick="duplicateRow()"  class="btn btn-primary"> + </a>
              <a href="javascript:void(0)" onclick="deleteRow(this)"  class="btn btn-danger"> - </a>
            </div>
          </div>
        @endif

        <input type="hidden" name="id_pessoa" value="{{ auth()->user()->pessoa->id }}">
        <input type="hidden" name="id_curso" value="{{ $curso->id }}">
        @if($empresa)<input type="hidden" name="id_empresa" value="{{ $empresa->id }}">@endif

        <button class="btn btn-primary mt-3 btn-cadastro">Confirmar meu cadastro</button>
      </form>


    </div>
  </div>
</div>

<script>

  function duplicateRow() {
    let row = $('.row-invite').last().clone()
    row.find('input').val('')
    row.insertAfter('.row-invite:last');
  }

  function deleteRow(elem){
    $(elem).closest('.row-invite').remove();
  }

  document.querySelectorAll('input[name="email[]"]').forEach(input => {
    input.addEventListener('input', function() {

      if(input.value != '') {
        document.querySelector('.btn-cadastro').textContent = "Confirmar meu cadastro e enviar inscrições";
        document.querySelectorAll('input[name="nome[]"]').forEach(nome => nome.required = true);
      } else {
        document.querySelector('.btn-cadastro').textContent = "Confirmar meu cadastro";
        document.querySelectorAll('input[name="nome[]"]').forEach(nome => nome.required = false);
      }
      
    });
  });

</script>
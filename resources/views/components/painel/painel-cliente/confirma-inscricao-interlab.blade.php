<div class="col-12 col-xxl-6 col-xl-8">
  @if($errors->any())
    @foreach($errors->all() as $error)
      <div class="alert alert-warning">{{ $error }}</div>
    @endforeach
  @endif

  <div class="card">
    <div class="card-body">
      @if( !$inscrito ) <h4 class="">Confirme sua inscrição:</h4> @endif
      <h5 class="card-subtitle mt-3 mb-2 text-primary-emphasis">Dados do interlaboratorial:</h5>

      <p class="pb-3">
        <strong>Interlaboratorial:</strong> {{ $interlab->interlab->nome }} <br>
        <strong>Agenda:</strong> de {{ \Carbon\Carbon::parse($interlab->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($interlab->data_fim)->format('d/m/Y') }} <br>
      </p>
      @if($interlab->inscricao_manual)
        <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-5">
          <i class="ri-information-fill text-primary fs-5"></i> Importante:
          <p class="mb-2 text-black">{!! nl2br($interlab->inscricao_manual) !!}</p>
        </blockquote>
      @endif

      @if($empresa)
        <div class="mt-3 mb-5">
          <h5 class="card-subtitle mb-2 text-primary-emphasis">Dados da empresa participante:</h5>
          <p>
            <strong>Razaão social:</strong> {{ $empresa->nome_razao }} <br>
            <strong>CNPJ:</strong> {{ $empresa->cpf_cnpj }} <br>
          </p>
        </div>
      @endif

      @if(!$empresa)
        <form action="{{ route('informa-empresa-interlab') }}" method="post" class="mb-5">
          @csrf
            <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-warning rounded">
              <p class="mb-2 text-black">Para prosseguir, você precisa informar o CNPJ da empresa participante.</p>
            </blockquote>
            <div class="row">
              <div class="col-8 col-xxl-6">
                <input type="text" class="form-control" name="cnpj" placeholder="CNPJ" id="input-cnpj">
                @error('cnpj')<div class="text-warning">{{ $message }}</div>@enderror
              </div>
              <div class="col-2">
                <button type="submit" class="btn btn-primary">Adicionar</button>
              </div>
            </div>
        </form>
      @endif
      
      @if($empresa )
        <form action="{{ route('confirma-inscricao-interlab') }}" method="post">
          @csrf

          @if( $convidado )
            <h4 class="">Confirme sua inscrição:</h4>
            <input type="hidden" name="interlab_uid" value="{{ $interlab->uid }}">
            <div class="my-3">
                @if( $inscrito ) 
                  <h5 class="text-muted">Você já está inscrito neste interlaboratorial.</h5>
                @else
                <p>Confirme seus dados de inscrição e clique em confirmar:</p>
                <div class="form-check bg-light rounded check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
                  <input type="hidden" name="inscrever_usuario_logado" value="1">
                  Nome: {{ auth()->user()->pessoa->nome_razao }} <br>
                  Email: {{ auth()->user()->email }} <br>
                  Telefone: {{ auth()->user()->pessoa->telefone ?? "-" }} <br>
                  CPF: {{ auth()->user()->pessoa->cpf_cnpj ?? "-" }} <br>
                  <div class="text-end">
                    <a href="{{ route('user-edit', auth()->user()->id)}}" class="link-primary fw-medium">
                      Editar meus dados
                      <i class="ri-arrow-right-line align-middle"></i>
                    </a>
                  </div>
                </div>

              @endif {{-- $inscrito --}}

          @else {{-- $convidado --}}
            @if( !$inscrito ) <h4 class="">Adicione os participantes:</h4> @endif
            <input type="hidden" name="interlab_uid" value="{{ $interlab->uid }}">
            <div class="my-3">
              @if( $inscrito )
                <h5 class="text-muted">Você já está inscrito neste interlaboratorial.</h5>

              @else
                <h6>Marque se quiser adicionar você como participante:</h6>
                <div class="form-check bg-light rounded check-bg" style="padding: 0.8rem 1.8rem 0.8rem;">
                  <input class="form-check-input" name="inscrever_usuario_logado" value="1" type="checkbox">
                  Nome: {{ auth()->user()->pessoa->nome_razao }} <br>
                  Email: {{ auth()->user()->email }} <br>
                  Telefone: {{ auth()->user()->pessoa->telefone ?? "-" }} <br>
                  CPF: {{ auth()->user()->pessoa->cpf_cnpj ?? "-" }} <br>
                  <div class="text-end">
                    <a href="{{ route('user-edit', auth()->user()->id)}}" class="link-primary fw-medium">
                      Editar meus dados
                      <i class="ri-arrow-right-line align-middle"></i>
                    </a>
                  </div>
                </div>

              @endif {{-- $inscrito --}}

            </div>

            <div>
              <h6>Adicione os outros participantes:</h6>
              <div class="row row-invite mt-1 gx-1">
                <div class="col-5">
                  <input type="text" class="form-control" name="indicacao_nome[]" placeholder="Nome" minlength="3">
                </div>
                <div class="col-5">
                  <input type="email" class="form-control" name="indicacao_email[]" placeholder="Email">
                </div>
                <div class="col-2">
                  <a href="javascript:void(0)" onclick="duplicateRow()" class="btn btn-primary"> + </a>
                  <a href="javascript:void(0)" onclick="deleteRow(this)" class="btn btn-danger"> - </a>
                </div>
              </div>
              <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-info rounded mt-2">
                <p>
                  <i class="ri-information-fill text-info fs-5"></i>
                  <span class="text-black">As pessoas adicionadas receberão um email com link para confirmarem suas inscrições.</span>
                </p>
              </blockquote>
            </div>
          @endif {{-- $convidado --}}
            <button class="btn btn-success mt-2" type="submit" >CONFIRMAR</button>
            <button class="btn btn-danger mt-2" type="button" onclick="limpaSessaoInterlab()">
              @if(session()->has('convites_enviados')) CONCLUIR INSCRIÇÕES @else CANCELAR @endif
            </button>
        </form>
      @endif
    </div>
  </div>
  <form action="{{ route('limpa-sessao-interlab')}}" method="post" id="limpa-sessao-interlab">@csrf</form>
</div>

<script>
  function duplicateRow() {
    let row = $('.row-invite').last().clone()
    row.find('input').val('')
    row.insertAfter('.row-invite:last');
  }

  function deleteRow(el){
    $(el).closest('.row-invite').remove();
  }

  document.getElementById('form-convite').addEventListener('submit', function(e){
    e.preventDefault();
      Swal.fire({
        title: 'Confirme os dados!',
        html: '<p class="fs-6"> Os convites serão enviados para os <b>nomes e emails informados</b>. <br> Não prefere revisar? </p>',
        icon: 'info',
        showCancelButton: true,
        cancelButtonText: 'Revisar',
        confirmButtonText: 'Já revisei, envie!',
        confirmButtonColor: '#2DCB73',
        cancelButtonColor: '#4AB0C1',
        reverseButtons: true,
      }).then((result) => {
        if (result.isConfirmed) {
          e.target.submit();
        }
      });

  });

  function limpaSessaoInterlab(){
    document.getElementById('limpa-sessao-interlab').submit();
  }
</script>
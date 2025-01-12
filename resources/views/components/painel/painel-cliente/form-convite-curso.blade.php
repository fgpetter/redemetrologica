<div class="alert alert-secondary alert-dismissible bg-body-secondary fade show" role="alert">
  <strong>IMPORTANTE: </strong> <br>
  <p>
    Para inscrever <strong>outras pessoas</strong> nesse curso, adicione os dados de nome e e-mail nos campos abaixo. <br>
    As pessoas adicionadas nessa lista receberão um email com link para confirmarem suas inscrições. <br>
  </p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<h6 class="card-subtitle my-3 text-primary-emphasis">Adicionar outros participantes da minha empresa:</h6>
<form action="{{ route('envia-convite-curso') }}" method="POST" id="form-convite">
  @csrf
  <input type="hidden" name="id_pessoa" value="{{ auth()->user()->pessoa->id }}">
  <input type="hidden" name="id_curso" value="{{ $curso->id }}">
  
  <div class="row row-invite mt-1 gx-1">
    <div class="col-5">
      <input type="text" class="form-control" name="indicacao-nome[]" placeholder="Nome" required>
    </div>
    <div class="col-5">
      <input type="email" class="form-control" name="indicacao-email[]" placeholder="Email"  required>
    </div>
    <div class="col-2">
      <a href="javascript:void(0)" onclick="duplicateRow()"  class="btn btn-primary"> + </a>
      <a href="javascript:void(0)" onclick="deleteRow(this)"  class="btn btn-danger"> - </a>
    </div>
  </div>
  <button class="btn btn-primary mt-3 btn-cadastro">Enviar Convites</button>
</form>

<script>

  function duplicateRow() {
    let row = $('.row-invite').last().clone()
    row.find('input').val('')
    row.insertAfter('.row-invite:last');
  }

  function deleteRow(elem){
    $(elem).closest('.row-invite').remove();
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
</script>
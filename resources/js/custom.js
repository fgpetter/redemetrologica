/**
 * Init Tooltip
 */
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

/**
 * iMasK input
 */
document.querySelectorAll('.table-cpf-cnpj').forEach(el => {
  IMask(el, {
    mask: [
      {mask: '000.000.000-00'},
      {mask: '00.000.000/0000-00'}
    ]
  })
});

document.querySelectorAll('.telefone').forEach(el => {
  IMask(el, {
    mask: [
      {mask: '(00)0000-0000'},
      {mask: '(00)00000-0000'}
    ]
  })
});

/**
 * Impede upload de arquivos maiores de 2MB
 */
const uploadField = document.getElementById("folder");
  if (uploadField){
    uploadField.onchange = function() {
      if(this.files[0].size > 2200000){
        alert("Tamanho máximo de arquivo: 2MB");
        this.value = "";
      };
  }
}

/**
 * Pega dados de campos de busca em tabelas e converte em url
 * para query no backend
 */
function search(e, url, tipo){
  if(e.keyCode === 13){
      e.preventDefault();
      url = url.split('?')[0] // remove parametros

      if(e.target.value != undefined){
        window.location.href = url+'?'+tipo+'='+e.target.value
      }
      
  }
}

function searchSelect(e, url, tipo){
  e.preventDefault();
  url = url.split('?')[0] // remove parametros

  if(e.target.value != undefined){
    window.location.href = url+'?'+tipo+'='+e.target.value
  }

}


window.onload = function(){

  if (window.jQuery) {

    /**
     * Redireciona para o link ao clicar duas vezes
     */
    $(".clicable").dblclick(function () {
      if( !$(this).attr('href') || $(this).attr('href') == undefined ) { return; }
      window.location.href = $(this).attr('href');
    });
    

    /**
     * Desabilita todos inputs de permissão quando selecionar admin
     */
    if($('#admin').prop('checked')) {
      disableOthers(true);
    }
    $('#admin').change(function () {
      if ($(this).prop('checked')) { disableOthers(true) }
      else { disableOthers(false) }
    });

    $("#avaliacoes, #cursos, #interlabs, #financeiro").change(function (){
      if ($(this).prop('checked')){
        $("#funcionario").prop('checked', true);
        $("#cliente").prop('checked', false);
      }
    })

    function disableOthers(status) {
      if (status == true) {
        $(".permission").prop('checked', false).prop('disabled', true);
        $(".text-admin").removeClass("d-none");
      }
      else {
        $(".permission").prop('disabled', false);
        $(".text-admin").addClass("d-none");
      }

    }

    /**
     * Aplica mascaras com jquery mask
    */
    if (window.jQuery.fn.mask) {
      $('#input-cnpj').mask('00.000.000/0000-00', {reverse: true});
      $('#input-cpf').mask('000.000.000-00', {reverse: true});
      $('.money').mask('0.000.000,00', {reverse: true});
      $('.cep').mask('00000-000');
    }

    /**
     * Carrega aba conforme URI
    */
    const anchor = window.location.hash;
    $(`a[href="${anchor}"]`).tab('show');
  }  // end if jQuery

  /**
   * Aplica sweet alert de exclusão
  */
  const deleteButton = document.querySelectorAll('.botao-delete')
  deleteButton.forEach(button => {

    button.addEventListener('click', function(e) {
      console.log(e.target.form)
      e.preventDefault();
      Swal.fire({
        title: 'Tem certeza?',
        text: "Você não poderá reverter isso!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir!'
      }).then((result) => {
        if (result.isConfirmed) {
          e.target.form.submit();
        }
      });
    });

  })
  

};
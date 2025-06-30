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

  /**
   * Carrega sweet alert de informações de mudança no site
  */
    const inscrevasseButton = document.querySelectorAll('.botao-inscrevase')
    inscrevasseButton.forEach(button => {
      console.log(button)
  
      button.addEventListener('click', function(e) {
        e.preventDefault();
        if (localStorage.getItem('inscrevase') == 'false') {
          window.location = e.target.getAttribute('href');
          return;
        }

        Swal.fire({
          title: 'Atenção!',
          html: `
            O sistema de inscrições da Rede Metrológica mudou. <br>
            Agora você faz apenas um <b>único cadastro</b> e gerencia suas inscrições dentro da sua área de cliente.
          `,
          text: "",
          icon: 'warning',
          iconColor: '#f7dc6f',
          iconHtml: '<i class="fas fa-exclamation-triangle" style="font-size: 3rem"></i>',
          showCancelButton: true,
          confirmButtonColor: '#3258d3',
          cancelButtonColor: '#adb5bd',
          confirmButtonText: 'Entendi!',
          cancelButtonText: 'Não mostrar novamente',
          reverseButtons: true,
          width: '40rem'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location = e.target.getAttribute('href');
          } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Salva no local storage que não deve mostrar novamente
            localStorage.setItem('inscrevase', 'false');
            window.location = e.target.getAttribute('href');
          } else if (result.dismiss !== Swal.DismissReason.cancel) {
            return;
          }
        });
      });
    })

  /**
   * Show Hide de card de dados IN COMPANY
   */
  tipoAgendamento = document.getElementById('tipo_agendamento')
  cardInCompany = document.getElementById('cursos-incompany')
  inputs = document.querySelectorAll("#inscricoes, #site, #investimento, #investimento_associado, #input-investimento_associado, #input-investimento, #input-site, #input-inscricoes")
  if(tipoAgendamento){
    tipoAgendamento.addEventListener("change", function(){
      if(tipoAgendamento.value == 'IN-COMPANY'){
        // exibe o card de inscrições incompany
        cardInCompany.classList.remove("d-none");
        // remove os campos de inscrições normais
        inputs.forEach(input => {
          input.value = ""
          input.classList.add("d-none")
        })
      } else {
        cardInCompany.classList.add("d-none");
        inputs.forEach(input => {
          input.classList.remove("d-none")
        })
      }
    })
  }

/**
 * Alterar fonte
 */
  const fontPlus = document.getElementById('font-plus')
  const fontMinus = document.getElementById('font-minus')
  const fontSize = document.body.style.fontSize
  if(!fontSize){
    document.body.style.fontSize = localStorage.getItem('fontSize') || '0.8rem'
  }
  fontPlus.addEventListener('click', function(){
    document.body.style.fontSize = parseFloat(localStorage.getItem('fontSize')) + 0.1 + 'rem'
    localStorage.setItem('fontSize', document.body.style.fontSize)
  })
  fontMinus.addEventListener('click', function(){
    document.body.style.fontSize = parseFloat(localStorage.getItem('fontSize')) - 0.1 + 'rem'
    localStorage.setItem('fontSize', document.body.style.fontSize)
  })

};
console.log('Custom JS loaded!')
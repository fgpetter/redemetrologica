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
      $('#input-cnpj').mask('00.000.000/0000-00');
      $('#input-cpf').mask('000.000.000-00');
      $('.money').mask('0.000.000,00', {reverse: true});
      $('.cep').mask('00000-000');
    }

    /**
     * Carrega aba conforme URI
    */
    const anchor = window.location.hash;
    if(anchor){
      $(`a[href="${anchor}"]`).tab('show');
    }
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
  if( fontPlus && fontMinus ){
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
  }

  const tomSelectSearchDefaults = {
    create: false,
    sortField: {
      field: 'text',
      direction: 'asc',
    },
    score: function(search) {
      const score = this.getScoreFunction(search);

      return function(item) {

        const text = item.text.toLowerCase();
        const term = search.toLowerCase();

        // prioridade máxima para itens que começam com o termo
        if (text.startsWith(term)) {
            return 1;
        }

        // reduz bastante itens que só contém o termo
        if (text.includes(term)) {
            return 0.2;
        }

        return score(item);
      };
    }

  }

  if (document.getElementById('tom-select')) {
    new TomSelect('#tom-select', tomSelectSearchDefaults)
  }

  const tomSelectBySelector = [
    '#tom-select-lancamento-pessoa',
    '#tom-select-lancamento-plano-conta',
    '#tom-select-agenda-interlab-empresa',
    '#tom-select-agenda-interlab-pessoa',
    '#tom-select-agendamento-curso-empresa-modal',
    '#tom-select-agendamento-curso-empresa-incompany',
    '#tom-select-avaliador-pessoa',
  ]
  tomSelectBySelector.forEach((selector) => {
    const el = document.querySelector(selector)
    if (el && !el.tomselect) {
      new TomSelect(el, tomSelectSearchDefaults)
    }
  })

};

console.log('Custom JS loaded!')
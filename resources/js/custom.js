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


window.onload = function(){
  if (window.jQuery && window.jQuery.fn.mask) {
    $('#input-cnpj').mask('00.000.000/0000-00', {reverse: true});
    $('#input-cpf').mask('000.000.000-00', {reverse: true});
    $('.money').mask('0.000.000,00', {reverse: true});
    $('.cep').mask('00000-000');
  }
};

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
 * Form de busca em tabelas
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



/**
 * Show Hide de card de dados IN COMPANY
 */
tipoAgendamento = document.getElementById('tipo_agendamento')
cardInCompany = document.getElementById('cursos-incompany')

if(tipoAgendamento){
  tipoAgendamento.addEventListener("change", function(){
    if(tipoAgendamento.value == 'IN-COMPANY'){
      cardInCompany.classList.remove("d-none");
    } else {
      cardInCompany.classList.add("d-none");
    }
  })
}

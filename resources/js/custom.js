/**
 * iMasK input
 */
if(document.querySelector("#input-cnpj")){
  IMask( document.querySelector("#input-cnpj"), {
    mask: '00.000.000/0000-00'
  })
}
if(document.querySelector("#input-cpf")){
  IMask(document.querySelector("#input-cpf"), {
    mask: '000.000.000-00'
  })
}

if(document.querySelector("#input-cep")){
  IMask(document.querySelector("#input-cep"), {
    mask: '00000-000'
  })
}

if(document.querySelector("#telefone")){
  IMask(document.querySelector("#telefone"), {
    mask: '(00) 0 0000-0000'
  })
}

if(document.querySelector("#telefone2")){
  IMask(document.querySelector("#telefone2"), {
  mask: '(00) 0 0000-0000'
})
}

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
      {mask: '(00) 0000-0000'},
      {mask: '(00) 0 0000-0000'}
    ]
  })
});

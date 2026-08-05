# Fluxos distintos de Carta Senha para Laboratório e Analista

O envio de Carta Senha misturava laboratório e analista num único mailable/view com condicionais, um Job duplicado e rastreio de envio apenas no inscrito. Decidimos bifurcar nos gatilhos: actions, jobs, mailables, views de e-mail e tipo de PDF próprios por destinatário (`tag_senha` vs `tag_senha_analista`), com `senha_enviada` também em `interlab_analistas`, para deixar os fluxos explícitos e o rastreio correto por analista.

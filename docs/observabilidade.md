# Guia de instrumentação de observabilidade

Use este padrão ao criar ou alterar fluxos operacionais, como comandos, crons, Jobs, integrações externas, comunicações, transações de negócio e geração de documentos.

## Defina onde começa a execução

- No ponto de entrada do fluxo, crie uma instância de [`ResumoExecucao`](../app/Support/Observabilidade/ResumoExecucao.php) com nome estável e chame `executar()`. Cada tentativa de um Job cria sua própria instância e usa `executar(..., job: true)`. O estado do resumo pertence àquela execução. Evite singletons e propriedades estáticas.
- Métodos chamados dentro da mesma execução recebem o resumo existente quando precisam registrar marcos próprios. Helpers de transformação e consultas auxiliares ficam cobertos pela etapa que os chama.
- Em lotes, mantenha contadores agregados no resumo principal. Quando cada item é uma operação independente e precisa de diagnóstico próprio, use `item()` ou o padrão existente de `ResumoExecucao::operacao()`. Cada item recebe `execucao_id` e `execucao_pai_id`. Guarde referências e falhas individuais no resumo do item.
- Para correlacionar operações assíncronas, reutilize as referências já disponíveis, como `tentativa_id`, `documento_id` e o UID do lote. Preserve os contratos dos payloads e evite consultas extras feitas só para compor telemetria.

## Registre etapas no resumo único

- Emita um evento `fluxo.execucao` por execução instrumentada. Use `etapa()` para acumular marcos como carregar registros, validar, preparar, enviar, persistir ou disponibilizar. Use nomes fixos em `snake_case` e reutilize o vocabulário do fluxo existente.
- Envolva o trabalho real na etapa correspondente. Quando um retorno normal representa impedimento ou ausência de trabalho, use `resultadoEtapa()` para marcar a etapa executada e `resultado()` para classificar o fluxo. Etapas que ficaram sem executar ficam ausentes do resumo.
- Use `incrementar()` para contadores numéricos. Etapas repetidas com o mesmo nome são agregadas. Evite logs operacionais por etapa ou listas de tamanho proporcional ao lote.
- Preserve o momento do commit. Mantenha a confirmação de sucesso pelo `ResumoExecucao`, que aguarda o commit quando há transação aberta. Enfileiramento, aceite pelo transporte e entrega ao destinatário são marcos distintos.

## Classifique o resultado e reporte a causa

- Use `concluido` para conclusão, `enfileirado` quando a operação termina na fila e `ignorado` com motivo estável para ausência normal de trabalho, como `sem_pendencias`. Esses resultados geram `INFO`.
- Use `impedido` para uma recusa de negócio e `retentativa` para uma falha transitória com nova tentativa. Ambos geram `WARNING`. Use tipos e motivos explícitos, como `OperacaoImpedidaException`, para identificar recusas conhecidas.
- Se o fluxo captura e consome uma exceção técnica, chame `$resumo->reportar($e)` uma vez no ponto que a trata. Isso associa a exceção ao `execucao_id` e restaura o contexto anterior. Registre `falha_definitiva` para falha terminal (`ERROR`) ou o resultado transitório aplicável. Use códigos fixos como motivo e mantenha o feedback funcional do fluxo.
- Para exceções que sobem ao Laravel, deixe o reporte com o Handler ou worker. Em Jobs, `executar(..., job: true)` marca a tentativa interrompida como `falha_execucao`. Preserve releases, retries e callbacks funcionais existentes, evitando reporte duplicado em `catch` ou `failed()`.

## Proteja os dados e preserve os canais

- Passe apenas referências técnicas necessárias e metadados permitidos por [`ContextoOperacional`](../app/Support/Observabilidade/ContextoOperacional.php). Evite contatos, documentos, segredos, URLs de acesso, payloads, corpos HTTP e mensagens livres de exceção ou de APIs no contexto do resumo, inclusive em arquivo local.
- Preserve os canais locais e especializados existentes. Use o roteamento seletivo do projeto para o Nightwatch. Registros funcionais existentes e eventos finais de lote continuam com sua finalidade própria.
- A amostragem do Nightwatch acompanha a execução que originou o log. A gravação local e a chegada ao painel são verificações distintas.

## Verifique os caminhos alterados

- Nos testes focados, confira a emissão única do resumo, as etapas alcançadas e seus resultados nos caminhos relevantes de sucesso, recusa ou falha.
- Ao consumir uma exceção, confira o reporte único, o mesmo `execucao_id` no resumo e no contexto do reporte e a restauração do contexto. Em lotes, verifique que a falha de um item mantém suas próprias referências mesmo quando outro item dá certo.
- Quando houver transação, confira que o rollback impede o resumo de sucesso. Para alterações de roteamento, confira os campos seguros no transporte de teste e a gravação local. Valide ingestão e conteúdo no painel com dados sintéticos em ambiente controlado.

## Exemplos no código

- [Financeiro em lote](../app/Actions/Financeiro/EditarLancamentosEmLoteAction.php) registra seleção, validação e atualização na mesma transação e usa o resumo recebido.
- [Rastreio de postagem](../app/Jobs/Interlab/RastrearItemLotePostagemJob.php) registra o resultado da tentativa e preserva a política de retentativa.
- [Comando de certificados Interlab](../app/Console/Commands/EnviarCertificadoInterlabPendenteCommand.php) acumula os resultados do lote e distingue a ausência de candidatos.

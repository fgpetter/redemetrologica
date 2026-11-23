# Rodada diária e envio de e-mails agendados

## Decisão

O scheduler inicia uma rodada por data local às 00h, em `America/Sao_Paulo`. A sequência é certificados de cursos, certificados Interlab, cartas de senha Interlab, lembretes de procedimento de laboratório, proposta de ações e ações corretivas, e e-mails automáticos de cursos. Uma etapa libera a próxima quando termina de gravar seus envios, mesmo que o provedor ainda não tenha aceitado nenhuma mensagem. As etapas rodam uma vez por dia e percorrem todos os candidatos elegíveis. O estado da rodada fica no banco e substitui os indicadores de ausência de pendências em cache.

Nos cursos, a data da rodada é a referência. O aviso de conferência considera início em dois dias. Pendências, informações e materiais consideram início no dia seguinte. O rastreio de postagens Interlab roda de forma independente às 06h nos dias úteis e seus avisos compartilham a fila de envio. Limpeza de uploads, limpeza de PDFs e retomada de lotes órfãos conservam seus agendamentos e fluxos. Envios manuais seguem seu encaminhamento atual.

Uma falha é registrada e as demais etapas podem seguir. Jobs de envio já gravados permanecem disponíveis e retentam de acordo com sua política. Uma etapa iniciada só roda automaticamente novamente na rodada do dia seguinte. A rodada foi dimensionada para acabar antes das 06h. Uma rodada ainda aberta nesse horário gera aviso.

Se o processo da rodada for interrompido, confirmar que ele terminou e executar `php artisan agendamentos:executar-rodada --retomar` no mesmo dia, após a liberação ou expiração da trava de seis horas. A retomada marca a etapa que estava em andamento como interrompida e executa somente as etapas que ainda não começaram.

## Worker

Usar `QUEUE_CONNECTION=database` e manter uma única instância do worker para a aplicação. Incluir na lista outras filas já atendidas pelo mesmo processo.

```sh
php artisan queue:work database --name=sistema-rede --queue=emails-agendados,default --sleep=1
```

O primeiro envio pode começar assim que o worker estiver livre. O horário mínimo do próximo é o aceite pelo provedor mais cinco segundos. Em caso de falha ou timeout, o intervalo conta do fim da tentativa. Durante a pausa, o worker pode atender outras filas. Uma retentativa segue seu atraso e, enquanto estiver indisponível, os próximos e-mails elegíveis podem avançar. Uma queda entre o aceite e a confirmação da resposta pode causar duplicidade em uma retentativa.

O aceite é a confirmação do transporte do provedor. Os transportes locais `log` e `array`, e o fallback configurado para `log`, não servem como confirmação. Para a fila agendada, configurar um transporte real de produção. Antes de ativar a rodada, drenar os envios agendados antigos, aplicar as migrações, atualizar o processo existente e reiniciá-lo.

## Operação e logs

Consultar `storage/logs/processos-agendados-AAAA-MM-DD.log` para ver início, marcos, fim, duração e resultado por processo. Usar o identificador da rodada e o da execução para ligar os eventos às tentativas de envio. Enfileiramento, aceite, retentativa e falha final são registrados separadamente. O rastreio tem seu próprio identificador de execução. Os logs dessa rotina são rotacionados diariamente, com retenção de 14 dias.

# Rodada diária e envio de e-mails agendados

## Agendamento

O Laravel inicia `agendamentos:executar-rodada` às 00h em `America/Sao_Paulo` com a proteção `withoutOverlapping()`. A rodada chama, em ordem, os comandos de certificados de cursos, certificados Interlab, cartas de senha Interlab, lembretes de procedimento de laboratório, proposta de ações e ações corretivas, e e-mails automáticos de cursos.

Cada comando libera o seguinte ao terminar de enfileirar seus e-mails. Se falhar, a rodada registra o erro, segue com os comandos restantes e retorna um código de falha ao final. Os jobs já gravados seguem a política normal da fila. Os registros das entidades continuam controlando a elegibilidade e a deduplicação. Os comandos de cursos usam a data da rodada, com dois dias de antecedência para a conferência e um dia para pendências, informações e materiais.

O rastreio de postagens Interlab continua independente às 06h nos dias úteis. Seus avisos usam a mesma fila de e-mails, fora da sequência de horários da rodada. Limpeza de uploads, limpeza de PDFs, recuperação de lotes órfãos e envios manuais conservam seus fluxos.

## Fila e disponibilidade

Os jobs usam a fila `default` da conexão nativa `database`. A rodada mantém em memória o próximo horário. Cada e-mail usa o maior valor entre esse horário e a hora atual. Uma gravação confirmada avança a referência em cinco segundos, inclusive entre comandos. Jobs e Mailables usam `delay()` e `Mail::later()`, respectivamente.

O intervalo vale para os horários de disponibilidade (`jobs.available_at`). Se o worker ficar atrasado ou indisponível, poderá processar em sequência vários jobs já disponíveis. O Laravel e o transporte configurado cuidam do envio, dos atrasos de retentativa e das falhas. Não há retomada seletiva da rodada. A proteção contra sobreposição vale para execuções iniciadas pelo scheduler.

## Operação

Em produção, configurar `QUEUE_CONNECTION=database`. A conexão mantém `retry_after=360` para atender também os jobs existentes com timeout de até 300 segundos. Manter um worker PHP atendendo `default` e as demais filas já configuradas.

```sh
php artisan queue:work database --name=sistema-rede --queue=default --sleep=1
```

Antes de retirar a fila `emails-agendados` da configuração do worker, drenar os jobs já gravados nela. Em instalações com o driver anterior, drenar também os jobs de rastreio pendentes e tratar as falhas de `App\Jobs\EnviarEmailAgendadoJob` que ainda precisem de retentativa. Após atualizar o código, atualizar os caches de configuração e eventos usados pelo deploy e reiniciar o worker existente. As tabelas auxiliares anteriormente aplicadas podem permanecer inativas, preservando seus dados.

## Logs

Consultar `storage/logs/processos-agendados-AAAA-MM-DD.log`, com retenção de 14 dias. O início e o fim de cada comando e da rodada usam o identificador da rodada, com duração, contagem de e-mails gravados, primeiro e último horários de disponibilidade e resultado. Exceções registram a classe obtida no ponto da falha.

Correlacionar `email.job.inicio` e `email.job.fim` por UUID e número da tentativa. A diferença entre `inicio_em` e `available_at`, em segundos, informa o atraso daquela tentativa. Comparar os inícios reais de jobs distintos da mesma rodada para identificar execuções próximas. O resultado do job descreve o processamento na fila.

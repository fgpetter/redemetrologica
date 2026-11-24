<?php

namespace Database\Seeders;

use App\Models\AgendaAvaliacao;
use App\Models\AgendaCursos;
use App\Models\CentroCusto;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Endereco;
use App\Models\Instrutor;
use App\Models\InterlabAnalista;
use App\Models\InterlabLaboratorio;
use App\Models\Laboratorio;
use App\Models\PlanoConta;
use Carbon\CarbonImmutable;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\LancamentoFinanceiroFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmailsAgendadosDevSeeder extends Seeder
{
    private const PREFIXO = 'emails-dev-';

    private const CERTIFICADOS_ELEGIVEIS = 15;

    private const CURSO_CERTIFICADO_INELEGIVEL = 6;

    public function run(): void
    {
        if (! app()->environment('local', 'development', 'testing')) {
            throw new RuntimeException('EmailsAgendadosDevSeeder só pode ser executado em local, development ou testing.');
        }

        $hoje = CarbonImmutable::today('America/Sao_Paulo');
        $dia = $hoje->isWeekend() ? $hoje->next(CarbonImmutable::MONDAY) : $hoje;

        DB::transaction(function () use ($dia): void {
            $pessoa = $this->persistirFactory(PessoaFactory::new(), 'contato', [
                'nome_razao' => 'Contato da rodada de desenvolvimento',
                'email' => 'contato@example.test',
                'email_cobranca' => 'cobranca@example.test',
                'site' => 'https://example.test',
            ]);

            $this->prepararCursos($dia, $pessoa->id);
            $this->prepararInterlab($dia, $pessoa->id);
            $this->prepararAvaliacoes($dia, $pessoa->id);
        });

        $this->command?->info('Cenários com UID '.self::PREFIXO.'* preparados para a rodada de '.$dia->toDateString().' (America/Sao_Paulo).');

        if ($hoje->isWeekend()) {
            $this->command?->warn('Fim de semana. Valide os lembretes na próxima data útil: '.$dia->toDateString().'.');
        }
    }

    private function prepararCursos(CarbonImmutable $dia, int $pessoaId): void
    {
        $curso = Curso::query()->updateOrCreate(['uid' => self::PREFIXO.'curso'], [
            'descricao' => 'Curso da rodada de desenvolvimento',
            'tipo_curso' => 'OFICIAL',
            'carga_horaria' => 8,
            'conteudo_programatico' => 'Conteúdo do curso de teste',
        ]);
        $instrutor = Instrutor::query()->updateOrCreate(['uid' => self::PREFIXO.'instrutor'], [
            'pessoa_id' => $pessoaId,
            'situacao' => true,
        ]);

        foreach ([
            'certificados' => [$dia->subDay(), 'REALIZADO', null],
            'conferencia' => [$dia->addDays(2), 'CONFIRMADO', null],
            'pendencias' => [$dia->addDay(), 'CONFIRMADO', null],
            'informacoes' => [$dia->addDay(), 'CONFIRMADO', 'https://example.test/aula'],
        ] as $cenario => [$data, $status, $link]) {
            $agendas[$cenario] = AgendaCursos::query()->updateOrCreate(['uid' => self::PREFIXO.'curso-'.$cenario], [
                'curso_id' => $curso->id,
                'instrutor_id' => $instrutor->id,
                'status' => $status,
                'tipo_agendamento' => 'ONLINE',
                'data_inicio' => $data->toDateString(),
                'data_fim' => $data->toDateString(),
                'horario' => '08:00',
                'link_aula' => $link,
                'conferencia_envio_id' => null,
                'conferencia_enviada_em' => null,
                'pendencias_envio_id' => null,
                'pendencias_enviadas_em' => null,
                'informacoes_enviadas_em' => null,
            ]);
        }

        $agendaCertificados = $agendas['certificados'];
        foreach (range(1, self::CERTIFICADOS_ELEGIVEIS + 1) as $numero) {
            $cenario = 'curso-certificado-'.$numero;
            $pagamento = $this->prepararPagamento($cenario, $pessoaId, [
                'agenda_curso_id' => $agendaCertificados->id,
                'centro_custo_id' => CentroCusto::ID_TREINAMENTO,
            ], $numero !== self::CURSO_CERTIFICADO_INELEGIVEL);

            CursoInscrito::query()->updateOrCreate(['uid' => self::PREFIXO.$cenario], [
                'agenda_curso_id' => $agendaCertificados->id,
                'pessoa_id' => $pessoaId,
                'nome' => "Participante curso {$numero}",
                'email' => "{$cenario}@example.test",
                'lancamento_financeiro_id' => $pagamento->id,
                'data_inscricao' => $dia->subDays(2)->addMinutes($numero),
                'valor' => 100,
                'certificado_emitido' => null,
                'certificado_envio_id' => null,
                'certificado_path' => null,
            ]);
        }

        $agendaInformacoes = $agendas['informacoes'];
        $agendaInformacoes->materiaisTreinamento()->updateOrCreate(['uid' => self::PREFIXO.'curso-apostila'], [
            'descricao' => 'Apostila de teste',
            'arquivo' => 'cursos/emails-dev/apostila.pdf',
        ]);
        foreach (range(1, 5) as $numero) {
            $cenario = $numero === 1 ? 'curso-participante' : "curso-participante-{$numero}";
            $agendaInformacoes->inscritos()->updateOrCreate(['uid' => self::PREFIXO.$cenario], [
                'pessoa_id' => $pessoaId,
                'nome' => "Participante informações do curso {$numero}",
                'email' => "{$cenario}@example.test",
                'data_inscricao' => $dia->subDay()->addMinutes($numero),
                'informacoes_envio_id' => null,
                'informacoes_enviadas_em' => null,
            ]);
        }
    }

    private function prepararInterlab(CarbonImmutable $dia, int $pessoaId): void
    {
        $endereco = Endereco::query()->updateOrCreate(['uid' => self::PREFIXO.'endereco'], [
            'pessoa_id' => $pessoaId,
            'cep' => '00000000',
        ]);
        $laboratorio = InterlabLaboratorio::query()->updateOrCreate(['uid' => self::PREFIXO.'interlab-lab'], [
            'nome' => 'Laboratório Interlab da rodada de desenvolvimento',
            'empresa_id' => $pessoaId,
            'endereco_id' => $endereco->id,
        ]);

        foreach ([
            'certificados' => ['CONCLUIDO', 'LABORATORIAL', 'DEVCE', $dia->subDays(2)],
            'laboratorio' => ['CONFIRMADO', 'LABORATORIAL', 'DEVLA', $dia->addDays(7)],
            'analista' => ['CONFIRMADO', 'ANALISTA', 'DEVAN', $dia->addDays(7)],
        ] as $cenario => [$status, $avaliacao, $tag, $data]) {
            $interlab = $this->persistirFactory(InterlabFactory::new(), 'interlab-'.$cenario, [
                'nome' => "Interlab de desenvolvimento ({$cenario})",
                'avaliacao' => $avaliacao,
                'tag' => $tag,
            ]);
            $agendas[$cenario] = $this->persistirFactory(AgendaInterlabFactory::new(), 'agenda-interlab-'.$cenario, [
                'interlab_id' => $interlab->id,
                'status' => $status,
                'data_inicio' => $data->toDateString(),
                'data_fim' => $data->addDay()->toDateString(),
                'data_limite_inscricao' => $data->subDay()->toDateString(),
                'ano_referencia' => $data->year,
            ]);
        }

        $inscricao = [
            'pessoa_id' => $pessoaId,
            'empresa_id' => $pessoaId,
            'laboratorio_id' => $laboratorio->id,
        ];

        foreach (range(1, self::CERTIFICADOS_ELEGIVEIS) as $numero) {
            $cenario = 'interlab-certificado-'.$numero;
            $agenda = $agendas['certificados'];
            $pagamento = $this->prepararPagamento($cenario, $pessoaId, [
                'agenda_interlab_id' => $agenda->id,
                'centro_custo_id' => CentroCusto::ID_INTERLABORATORIAL,
            ], true);
            $this->persistirFactory(InterlabInscritoFactory::new(), $cenario, $inscricao + [
                'agenda_interlab_id' => $agenda->id,
                'lancamento_financeiro_id' => $pagamento->id,
                'valor' => 100,
                'email' => "{$cenario}@example.test",
                'data_inscricao' => $dia->subDays(3)->addMinutes($numero),
                'certificado_emitido' => null,
                'certificado_envio_id' => null,
                'certificado_path' => null,
            ]);
        }

        $this->persistirFactory(InterlabInscritoFactory::new(), 'senha-laboratorio', $inscricao + [
            'agenda_interlab_id' => $agendas['laboratorio']->id,
            'email' => 'senha-laboratorio@example.test',
            'tag_senha' => 'DEVLA001',
            'senha_enviada' => null,
        ]);
        $inscritoAnalista = $this->persistirFactory(InterlabInscritoFactory::new(), 'senha-analista', $inscricao + [
            'agenda_interlab_id' => $agendas['analista']->id,
            'email' => 'inscricao-analista@example.test',
        ]);
        InterlabAnalista::query()->updateOrCreate(['uid' => self::PREFIXO.'analista'], [
            'interlab_inscrito_id' => $inscritoAnalista->id,
            'nome' => 'Analista da rodada de desenvolvimento',
            'email' => 'senha-analista@example.test',
            'telefone' => '11900000000',
            'tag_senha' => 'DEVAN001',
            'senha_enviada' => null,
        ]);
    }

    private function prepararAvaliacoes(CarbonImmutable $dia, int $pessoaId): void
    {
        $prazo = $dia->addDays($dia->isFriday() ? 3 : 1)->toDateString();

        foreach ([
            'procedimento' => 'data_proc_laboratorio',
            'proposta' => 'data_proposta_acoes_corretivas',
            'corretivas' => 'data_acoes_corretivas',
        ] as $cenario => $coluna) {
            $laboratorio = Laboratorio::query()->updateOrCreate(['uid' => self::PREFIXO.'lab-'.$cenario], [
                'pessoa_id' => $pessoaId,
                'nome_laboratorio' => "Laboratório de teste ({$cenario})",
                'contato' => 'Contato de teste',
                'telefone' => '11900000000',
                'email' => "avaliacao-{$cenario}@example.test",
                'responsavel_tecnico' => 'Responsável de teste',
            ]);
            AgendaAvaliacao::query()->updateOrCreate(['uid' => self::PREFIXO.'avaliacao-'.$cenario], [
                'laboratorio_id' => $laboratorio->id,
                'data_inicio' => $dia->subWeek()->toDateString(),
                'perc_lucro' => 15,
                $coluna => $prazo,
            ]);
        }
    }

    private function prepararPagamento(string $cenario, int $pessoaId, array $referencia, bool $efetivado): Model
    {
        return $this->persistirFactory(LancamentoFinanceiroFactory::new(), 'pagamento-'.$cenario, [
            'pessoa_id' => $pessoaId,
            'historico' => 'Rodada de desenvolvimento - '.$cenario,
            'plano_conta_id' => PlanoConta::query()->find(PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS)?->id,
            'centro_custo_id' => CentroCusto::query()->find($referencia['centro_custo_id'])?->id,
            'status' => $efetivado ? 'EFETIVADO' : 'PROVISIONADO',
        ] + $referencia);
    }

    private function persistirFactory(Factory $factory, string $cenario, array $atributos): Model
    {
        $uid = self::PREFIXO.$cenario;
        $registro = $factory->newModel()->newQuery()->where('uid', $uid)->first();

        if ($registro === null) {
            return $factory->create(['uid' => $uid] + $atributos);
        }

        $registro->update($atributos);

        return $registro;
    }
}

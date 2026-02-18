<?php

namespace App\Actions;

use App\Models\Endereco;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\AgendaInterlab;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmacaoInscricaoAnalistaNotification;
use App\Mail\SenhaAnalistaInterlabNotification;

class InscricaoInterlabAction
{
    public function execute(AgendaInterlab $agendaInterlab, array $dados, array $analistas = []): InterlabInscrito
    {
        return DB::transaction(function () use ($agendaInterlab, $dados, $analistas) {
            $laboratorio = $this->criarOuAtualizarLaboratorio($agendaInterlab, $dados);
            $inscrito = $this->criarInscrito($agendaInterlab, $laboratorio, $dados);
            $this->criarAnalistas($inscrito, $agendaInterlab, $analistas);

            return $inscrito;
        });
    }

    protected function criarOuAtualizarLaboratorio(AgendaInterlab $agendaInterlab, array $dados): InterlabLaboratorio
    {
        $lab = $dados['laboratorio'];
        $enderecoData = $lab['endereco'] ?? [];
        $interlabNome = $agendaInterlab->interlab->nome ?? '';
        $info = 'Laboratório: ' . ($lab['nome'] ?? '') . ' | Inscrito no PEP: ' . $interlabNome;

        if (empty($dados['laboratorio_id'])) {
            $endereco = Endereco::create([
                'pessoa_id' => $dados['empresa_id'],
                'info' => $info,
                'cep' => $enderecoData['cep'] ?? '',
                'endereco' => $enderecoData['endereco'] ?? '',
                'complemento' => $enderecoData['complemento'] ?? null,
                'bairro' => $enderecoData['bairro'] ?? '',
                'cidade' => $enderecoData['cidade'] ?? '',
                'uf' => $enderecoData['uf'] ?? '',
            ]);

            return InterlabLaboratorio::create([
                'empresa_id' => $dados['empresa_id'],
                'endereco_id' => $endereco->id,
                'nome' => $lab['nome'],
            ]);
        }

        $laboratorio = InterlabLaboratorio::find($dados['laboratorio_id']);
        $laboratorio->update(['nome' => $lab['nome']]);

        if ($laboratorio->endereco) {
            $laboratorio->endereco->update([
                'cep' => $enderecoData['cep'] ?? '',
                'endereco' => $enderecoData['endereco'] ?? '',
                'complemento' => $enderecoData['complemento'] ?? null,
                'bairro' => $enderecoData['bairro'] ?? '',
                'cidade' => $enderecoData['cidade'] ?? '',
                'uf' => $enderecoData['uf'] ?? '',
                'info' => $info,
            ]);
        }

        return $laboratorio;
    }

    protected function criarInscrito(AgendaInterlab $agendaInterlab, InterlabLaboratorio $laboratorio, array $dados): InterlabInscrito
    {
        $lab = $dados['laboratorio'];
        $senha = ! empty($agendaInterlab->interlab?->tag)
            ? $this->geraTagSenha($agendaInterlab, fn (string $s) => InterlabInscrito::where('tag_senha', $s)->where('agenda_interlab_id', $agendaInterlab->id)->exists())
            : null;

        return InterlabInscrito::create([
            'pessoa_id' => $dados['pessoa_id'],
            'empresa_id' => $dados['empresa_id'],
            'laboratorio_id' => $laboratorio->id,
            'agenda_interlab_id' => $agendaInterlab->id,
            'data_inscricao' => now(),
            'valor' => $dados['valor'] ?? null,
            'informacoes_inscricao' => $dados['informacoes_inscricao'] ?? '',
            'tag_senha' => $senha,
            'responsavel_tecnico' => $lab['responsavel_tecnico'] ?? '',
            'telefone' => $this->normalizaTelefone($lab['telefone'] ?? null),
            'email' => $lab['email'] ?? '',
        ]);
    }

    protected function criarAnalistas(InterlabInscrito $inscrito, AgendaInterlab $agendaInterlab, array $analistas): void
    {
        $requerAnalistas = ($agendaInterlab->interlab->avaliacao ?? null) === 'ANALISTA';

        if (! $requerAnalistas || empty($analistas)) {
            return;
        }

        foreach ($analistas as $analistaData) {
            if (empty($analistaData['nome'])) {
                continue;
            }

            $tagSenha = null;
            if (! empty($agendaInterlab->interlab->tag)) {
                $tagSenha = $this->geraTagSenha($agendaInterlab, 'interlab_analistas');
            }

            $analista = InterlabAnalista::create([
                'interlab_inscrito_id' => $inscrito->id,
                'nome' => $analistaData['nome'],
                'email' => $analistaData['email'] ?? '',
                'telefone' => $this->normalizaTelefone($analistaData['telefone'] ?? ''),
                'tag_senha' => $tagSenha,
            ]);

            if (! empty($tagSenha)) {
                Mail::to($analista->email)->send(new SenhaAnalistaInterlabNotification($analista, $agendaInterlab));
            }

            Mail::to($analista->email)->send(new ConfirmacaoInscricaoAnalistaNotification($analista, $inscrito, $agendaInterlab));
        }
    }

    protected function geraTagSenha(AgendaInterlab $agendaInterlab, string $tipo): string
    {
        $tag = $agendaInterlab->interlab->tag ?? throw new \Exception('Tag do interlab não encontrada');

        if ($tipo === 'interlab_inscritos') {
            while (InterlabInscrito::where('tag_senha', $senha)->where('agenda_interlab_id', $agendaInterlab->id)->exists()) {
                $senha = $tag . '-' . rand(111, 999);
            }
        }

        if ($tipo === 'interlab_analistas') {
            while (InterlabAnalista::where('tag_senha', $senha)) {
                $senha = $tag . '-' . rand(111, 999);
            }
        }

        return $senha;
    }

    protected function normalizaTelefone(?string $telefone): ?string
    {
        if (empty($telefone)) {
            return null;
        }

        return preg_replace('/\D/', '', $telefone) ?: null;
    }
}

<?php

namespace App\Actions;

use App\Models\Endereco;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\AgendaInterlab;
use Illuminate\Support\Facades\DB;

class InscricaoInterlabAction
{
    public function execute(AgendaInterlab $agendaInterlab, array $dados, array $analistas = []): InterlabInscrito
    {
        return DB::transaction(function () use ($agendaInterlab, $dados, $analistas) {
            $laboratorio = $this->criarOuAtualizarLaboratorio($agendaInterlab, $dados);
            $inscrito = $this->criarOuAtualizarInscrito($agendaInterlab, $laboratorio, $dados);
            $this->criarOuAtualizarAnalistas($inscrito, $agendaInterlab, $analistas);

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

    protected function criarOuAtualizarInscrito(AgendaInterlab $agendaInterlab, InterlabLaboratorio $laboratorio, array $dados): InterlabInscrito
    {
        $lab = $dados['laboratorio'];
        $camposComuns = [
            'laboratorio_id' => $laboratorio->id,
            'valor' => $dados['valor'] ?? null,
            'informacoes_inscricao' => $dados['informacoes_inscricao'] ?? '',
            'responsavel_tecnico' => $lab['responsavel_tecnico'] ?? '',
            'telefone' => $this->normalizaTelefone($lab['telefone'] ?? null),
            'email' => $lab['email'] ?? '',
        ];

        if (! empty($dados['inscrito_id'])) {
            $inscrito = InterlabInscrito::find($dados['inscrito_id']);
            $inscrito->update($camposComuns);

            return $inscrito;
        }

        $senha = ! empty($agendaInterlab->interlab?->tag)
            ? $this->geraTagSenha($agendaInterlab, 'interlab_laboratorios')
            : null;

        return InterlabInscrito::create(array_merge($camposComuns, [
            'pessoa_id' => $dados['pessoa_id'],
            'empresa_id' => $dados['empresa_id'],
            'agenda_interlab_id' => $agendaInterlab->id,
            'data_inscricao' => now(),
            'tag_senha' => $senha,
        ]));
    }

    protected function criarOuAtualizarAnalistas(InterlabInscrito $inscrito, AgendaInterlab $agendaInterlab, array $analistas): void
    {
        if (($agendaInterlab->interlab->avaliacao ?? null) !== 'ANALISTA') {
            return;
        }

        InterlabAnalista::where('interlab_inscrito_id', $inscrito->id)->delete();

        foreach ($analistas as $analistaData) {
            if (empty($analistaData['nome'])) {
                continue;
            }

            $tagSenha = ! empty($agendaInterlab->interlab->tag)
                ? $this->geraTagSenha($agendaInterlab, 'interlab_analistas')
                : null;

            InterlabAnalista::create([
                'interlab_inscrito_id' => $inscrito->id,
                'nome' => $analistaData['nome'],
                'email' => $analistaData['email'] ?? '',
                'telefone' => $this->normalizaTelefone($analistaData['telefone'] ?? ''),
                'tag_senha' => $tagSenha,
            ]);
        }
    }

    protected function geraTagSenha(AgendaInterlab $agendaInterlab, string $tipo): string
    {
        $tag = $agendaInterlab->interlab->tag ?? throw new \Exception('Tag do interlab não encontrada');
        $senha = $tag . '-' . rand(111, 999);

        if ($tipo === 'interlab_laboratorios') {
            while (InterlabInscrito::where('tag_senha', $senha)->where('agenda_interlab_id', $agendaInterlab->id)->exists()) {
                $senha = $tag . rand(111, 999);
            }
        }

        if ($tipo === 'interlab_analistas') {
            while (InterlabAnalista::where('tag_senha', $senha)->exists()) {
                $senha = $tag . rand(111, 999);
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

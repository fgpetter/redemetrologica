<?php

namespace App\Livewire\Cursos;

use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ListParticipantes extends Component
{
    public AgendaCursos $agendacurso;

    public string $sortBy = 'empresa';

    public string $sortDirection = 'ASC';

    /**
     * Define o campo de ordenação dos participantes e empresas
     */
    public function setSortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'ASC';
        }
    }

    /**
     * Renderiza a tela de lista de participantes
     */
    public function render()
    {
        $inscritosQuery = $this->agendacurso->inscritos()->with(['empresa', 'lancamentoFinanceiro']);

        if ($this->sortBy === 'nome') {
            $inscritosQuery->orderBy('nome', $this->sortDirection);
        } elseif ($this->sortBy === 'empresa') {
            $inscritosQuery->leftJoin('pessoas as empresa_pessoas', 'curso_inscritos.empresa_id', '=', 'empresa_pessoas.id')
                ->orderBy('empresa_pessoas.nome_razao', $this->sortDirection)
                ->select('curso_inscritos.*');
        } else {
            $inscritosQuery->orderBy($this->sortBy, $this->sortDirection);
        }

        return view('livewire.cursos.list-participantes', [
            'inscritos' => $inscritosQuery->get(),
        ]);
    }

    public $nome;

    public $email;

    public $telefone;

    public function saveInscrito()
    {
        $this->validate([
            'nome' => 'required|string|min:3',
            'email' => 'required|email',
            'telefone' => 'nullable|string',
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail informado é inválido.',
        ]);

        $nome = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($this->nome));
        $email = strtolower(trim($this->email));
        $telefone = preg_replace('/[^0-9]/', '', $this->telefone ?? '');

        if (function_exists('isInvalidEmail') && isInvalidEmail($email)) {
            $this->addError('email', 'E-mail inválido.');

            return;
        }

        DB::transaction(function () use ($nome, $email, $telefone) {
            $empresa = $this->agendacurso->empresa_id
                ? Pessoa::query()->find($this->agendacurso->empresa_id)
                : null;

            $valorBase = $empresa && (int) $empresa->associado === 1
                ? $this->agendacurso->investimento_associado
                : $this->agendacurso->investimento;

            $cursoInscrito = CursoInscrito::create([
                'pessoa_id' => auth()->user()->pessoa->id,
                'agenda_curso_id' => $this->agendacurso->id,
                'empresa_id' => $this->agendacurso->empresa_id,
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone ?: null,
                'valor' => $valorBase,
                'data_inscricao' => now(),
            ]);

            if ($empresa) {
                $lancamento = LancamentoFinanceiro::query()
                    ->where('pessoa_id', $empresa->id)
                    ->where('agenda_curso_id', $this->agendacurso->id)
                    ->first();

                if (! $lancamento) {
                    $lancamento = LancamentoFinanceiro::create([
                        'pessoa_id' => $empresa->id,
                        'agenda_curso_id' => $this->agendacurso->id,
                        'historico' => 'Inscrição no curso - '.$this->agendacurso->curso->descricao,
                        'valor' => formataMoeda($valorBase),
                        'centro_custo_id' => '3',
                        'plano_conta_id' => '3',
                        'data_emissao' => now(),
                        'status' => 'PROVISIONADO',
                        'observacoes' => 'Inscrição de '.$nome.', com valor de R$ '.formataMoeda($valorBase).', em '.now()->format('d/m/Y H:i'),
                    ]);
                } else {
                    $inscritosEmpresa = CursoInscrito::query()
                        ->where('empresa_id', $empresa->id)
                        ->where('agenda_curso_id', $this->agendacurso->id)
                        ->get();

                    $observacoes = '';
                    foreach ($inscritosEmpresa as $dado) {
                        $data = Carbon::parse($dado->data_inscricao)->format('d/m/Y H:i');
                        $observacoes .= "Inscrição de {$dado->nome}, com valor de R$ {$dado->valor}, em {$data} \n";
                    }

                    $lancamento->update([
                        'valor' => $inscritosEmpresa->sum('valor'),
                        'observacoes' => $observacoes,
                    ]);
                }
            } else {
                $pessoaUsuario = auth()->user()->pessoa;

                $lancamento = LancamentoFinanceiro::updateOrCreate(
                    [
                        'pessoa_id' => $pessoaUsuario->id,
                        'agenda_curso_id' => $this->agendacurso->id,
                    ],
                    [
                        'historico' => 'Inscrição no curso - '.$this->agendacurso->curso->descricao,
                        'valor' => formataMoeda($valorBase),
                        'centro_custo_id' => '3',
                        'plano_conta_id' => '3',
                        'data_emissao' => now(),
                        'status' => 'PROVISIONADO',
                        'observacoes' => 'Inscrição de '.$nome.', em '.now()->format('d/m/Y H:i'),
                    ]
                );
            }

            $cursoInscrito->update([
                'lancamento_financeiro_id' => $lancamento->id,
            ]);
        });

        $this->reset(['nome', 'email', 'telefone']);
        session()->flash('success', 'Inscrito adicionado com sucesso!');
    }

    public function enviarDocs(CursoInscrito $inscrito)
    {
        try {
            app(\App\Actions\EnviarMaterialCursoAction::class)->execute($inscrito);
            session()->flash('success', 'E-mail com materiais enviado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', 'Houve um erro ao tentar enviar os materiais: '.$e->getMessage());
        }
    }

    public function enviarCertificado(CursoInscrito $inscrito)
    {
        try {
            app(\App\Actions\EnviarCertificadoAction::class)->execute($inscrito);
            session()->flash('success', 'E-mail com certificado enviado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', 'Houve um erro ao tentar enviar o certificado: '.$e->getMessage());
        }
    }
}

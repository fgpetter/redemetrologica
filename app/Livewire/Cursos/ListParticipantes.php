<?php

namespace App\Livewire\Cursos;

use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\Pessoa;
use App\Actions\CreateUserForPessoaAction;
use Illuminate\Support\Facades\DB;
use LaravelLegends\PtBrValidator\Rules\CpfOuCnpj;
use Livewire\Component;

class ListParticipantes extends Component
{
    public AgendaCursos $agendacurso;
    public string $sortBy = 'empresa';
    public string $sortDirection = 'ASC';

    /**
     * Define o campo de ordenação dos participantes e empresas
     *
     * @param string $field
     * @return void
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
     * 
     */
    public function render()
    {
        // Consulta os inscritos com as pessoas e empresas
        $inscritosQuery = $this->agendacurso->inscritos()->with(['pessoa', 'empresa']);

        if ($this->sortBy === 'nome') {
            $inscritosQuery->join('pessoas', 'curso_inscritos.pessoa_id', '=', 'pessoas.id')
                ->orderBy('pessoas.nome_razao', $this->sortDirection)
                ->select('curso_inscritos.*');
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

    public $cpf_cnpj;
    public $nome_razao;
    public $email;

    public function saveInscrito()
    {
        $this->validate([
            'cpf_cnpj' => ['required', new CpfOuCnpj],
            'nome_razao' => 'required|string|min:3',
            'email' => 'required|email',
        ], [
            'cpf_cnpj.required' => 'O CPF é obrigatório.',
            'cpf_cnpj.CpfOuCnpj' => 'O CPF informado é inválido.',
            'nome_razao.required' => 'O nome é obrigatório.',
            'nome_razao.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail informado é inválido.',
        ]);

        DB::transaction(function () {
            // Remove caracteres especiais e espaços extras
            $cpf_cnpj = preg_replace('/[^0-9]/', '', $this->cpf_cnpj);
            $nome_razao = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($this->nome_razao));
            $email = strtolower($this->email);

            // Pula se o e-mail for inválido
            if (function_exists('isInvalidEmail') && isInvalidEmail($email)) {
                $this->addError('email', 'E-mail inválido.');
                return;
            }

            //Verifica se a pessoa já existe ou cria uma nova
            $pessoa = Pessoa::updateOrCreate(
                ['cpf_cnpj' => $cpf_cnpj],
                [
                    'nome_razao' => $nome_razao,
                    'email' => $email,
                    'tipo_pessoa' => 'PF'
                ]
            );

            // Cria o usuário para a pessoa
            CreateUserForPessoaAction::handle($pessoa);

            // Cria ou atualiza o registro de inscrição
            CursoInscrito::updateOrCreate([
                'pessoa_id' => $pessoa->id,
                'agenda_curso_id' => $this->agendacurso->id,
            ], [
                'empresa_id' => $this->agendacurso->empresa_id,
                'data_inscricao' => now()
            ]);

            $this->reset(['cpf_cnpj', 'nome_razao', 'email']);
            session()->flash('success', 'Inscrito adicionado com sucesso!');
        });
    }
}

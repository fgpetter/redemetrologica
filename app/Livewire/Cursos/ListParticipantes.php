<?php

namespace App\Livewire\Cursos;

use App\Actions\SalvaInscritoInCompanyAction;
use App\Helpers\Telefone;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
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
        $this->telefone = Telefone::paraValidacao($this->telefone);
        $this->validate([
            'nome' => 'required|string|min:3',
            'email' => 'required|email',
            'telefone' => ['nullable', 'celular_com_ddd'],
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail informado é inválido.',
            'telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
        ]);

        $nome = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($this->nome));
        $email = strtolower(trim($this->email));
        $telefone = Telefone::somenteNumeros($this->telefone);

        if (function_exists('isInvalidEmail') && isInvalidEmail($email)) {
            $this->addError('email', 'E-mail inválido.');

            return;
        }

        app(SalvaInscritoInCompanyAction::class)->criar(
            $this->agendacurso,
            $nome,
            $email,
            $telefone ?: null
        );

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

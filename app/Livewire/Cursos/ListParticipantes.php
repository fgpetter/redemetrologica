<?php

namespace App\Livewire\Cursos;

use App\Actions\EnviarCertificadoAction;
use App\Actions\SalvaInscritoInCompanyAction;
use App\Helpers\Telefone;
use App\Livewire\Forms\ParticipanteCursoForm;
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
        if (! in_array($field, ['data_inscricao', 'empresa', 'nome'], true)) {
            return;
        }

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
        $this->sortBy = in_array($this->sortBy, ['data_inscricao', 'empresa', 'nome'], true) ? $this->sortBy : 'empresa';
        $this->sortDirection = in_array($this->sortDirection, ['ASC', 'DESC'], true) ? $this->sortDirection : 'ASC';
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

    public ParticipanteCursoForm $form;

    public $nome;

    public $email;

    public $telefone;

    public function saveInscrito()
    {
        abort_unless(auth()->user()?->hasPermissionTo(['funcionario', 'admin']), 404);
        $this->telefone = Telefone::paraValidacao($this->telefone);
        $this->form->fill(['nome' => $this->nome, 'email' => $this->email, 'telefone' => $this->telefone]);
        $this->validate($this->form->rules(), $this->form->messages());

        $nome = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($this->form->nome));
        $email = strtolower(trim($this->form->email));
        $telefone = Telefone::somenteNumeros($this->form->telefone);

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

    public function enviarCertificado(CursoInscrito $inscrito)
    {
        abort_unless(auth()->user()?->hasPermissionTo(['funcionario', 'admin']), 404);
        $inscrito = $this->agendacurso->inscritos()->findOrFail($inscrito->id);
        try {
            app(EnviarCertificadoAction::class)->execute($inscrito);
            session()->flash('success', 'E-mail com certificado enviado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', 'Houve um erro ao tentar enviar o certificado: '.$e->getMessage());
        }
    }
}

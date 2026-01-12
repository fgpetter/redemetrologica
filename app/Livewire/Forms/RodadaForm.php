<?php

namespace App\Livewire\Forms;

use App\Models\InterlabRodada;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RodadaForm extends Form
{
    public ?int $id = null;
    public ?string $uid = null;

    #[Validate('required|integer', message: 'Houve um erro ao salvar. Agenda inexistente')]
    public ?int $agenda_interlab_id = null;

    #[Validate('required|string', message: [
        'required' => 'O campo descrição é obrigatório',
        'string' => 'O campo descrição permite somente texto',
    ])]
    public string $descricao = '';

    #[Validate('required|numeric|min:1', message: [
        'required' => 'O campo vias deve ser preenchido',
        'numeric' => 'O campo vias deve ser um número',
        'min' => 'O campo vias deve ser maior que 0',
    ])]
    public int $vias = 1;

    #[Validate('nullable|date', message: 'O campo data de envio de amostras deve ser uma data válida')]
    public ?string $data_envio_amostras = null;

    #[Validate('nullable|date', message: 'O campo data de início de ensaios deve ser uma data válida')]
    public ?string $data_inicio_ensaios = null;

    #[Validate('nullable|date', message: 'O campo data de limite de envio de resultados deve ser uma data válida')]
    public ?string $data_limite_envio_resultados = null;

    #[Validate('nullable|date', message: 'O campo data de divulgação de relatórios deve ser uma data válida')]
    public ?string $data_divulgacao_relatorios = null;

    /**
     * Preenche o form com dados de uma rodada existente
     */
    public function setRodada(InterlabRodada $rodada): void
    {
        $this->id = $rodada->id;
        $this->uid = $rodada->uid;
        $this->agenda_interlab_id = $rodada->agenda_interlab_id;
        $this->descricao = $rodada->descricao;
        $this->vias = $rodada->vias;
        $this->data_envio_amostras = $rodada->data_envio_amostras;
        $this->data_inicio_ensaios = $rodada->data_inicio_ensaios;
        $this->data_limite_envio_resultados = $rodada->data_limite_envio_resultados;
        $this->data_divulgacao_relatorios = $rodada->data_divulgacao_relatorios;
    }

    /**
     * Inicializa o form para uma nova rodada
     */
    public function setNew(int $agendaInterlabId): void
    {
        $this->uid = uniqid();
        $this->agenda_interlab_id = $agendaInterlabId;
        $this->descricao = '';
        $this->vias = 1;
        $this->data_envio_amostras = null;
        $this->data_inicio_ensaios = null;
        $this->data_limite_envio_resultados = null;
        $this->data_divulgacao_relatorios = null;
    }

    /**
     * Retorna os dados do form para persistência (excluindo parametros)
     */
    public function toArray(): array
    {
        return [
            'uid' => $this->uid,
            'agenda_interlab_id' => $this->agenda_interlab_id,
            'descricao' => $this->descricao,
            'vias' => $this->vias,
            'data_envio_amostras' => $this->data_envio_amostras,
            'data_inicio_ensaios' => $this->data_inicio_ensaios,
            'data_limite_envio_resultados' => $this->data_limite_envio_resultados,
            'data_divulgacao_relatorios' => $this->data_divulgacao_relatorios,
        ];
    }
}


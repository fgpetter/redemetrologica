<?php

namespace App\Livewire\Interlab;

use App\Actions\EnviarCertificadoInterlabAction;
use App\Actions\Financeiro\GerarLancamentoInterlabAction;
use App\Models\AgendaInterlab;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Livewire\Component;

class ListParticipantes extends Component
{
    public int $idinterlab;

    public AgendaInterlab $agendainterlab;

    public ?int $certificadoParticipanteId = null;

    public string $certificadoEmail = '';

    public bool $showCertificadoModal = false;

    #[On('refresh-interlab-participantes')]
    public function refreshParticipantesList(): void
    {
        // Re-renderiza a listagem após edição no OffCanvas.
    }

    public function mount(int $idinterlab, AgendaInterlab $agendainterlab): void
    {
        $this->idinterlab = $idinterlab;
        $this->agendainterlab = $agendainterlab;
    }

    public function confirmarEnvioCertificado(int $participanteId, string $email = ''): void
    {
        $this->certificadoParticipanteId = $participanteId;
        $this->certificadoEmail = $email !== '' ? $email : (InterlabInscrito::query()->find($participanteId)?->email ?? '');
        $this->showCertificadoModal = true;
    }

    public function enviarCertificado(): void
    {
        $this->validate([
            'certificadoEmail' => 'required|email|max:191',
        ], [
            'certificadoEmail.required' => 'O email é obrigatório.',
            'certificadoEmail.email' => 'O email deve ser um endereço válido.',
            'certificadoEmail.max' => 'O email deve ter no máximo 191 caracteres.',
        ]);

        try {
            $inscrito = InterlabInscrito::query()->findOrFail($this->certificadoParticipanteId);
            app(EnviarCertificadoInterlabAction::class)->execute($inscrito, $this->certificadoEmail);
            $this->fecharCertificadoModal();
            $this->dispatch('show-success-alert', message: 'Certificado está sendo gerado e será enviado por email em breve.');
        } catch (\Exception $e) {
            $this->dispatch('show-error-alert', message: 'Erro ao gerar certificado: '.$e->getMessage());
        }
    }

    public function fecharCertificadoModal(): void
    {
        $this->showCertificadoModal = false;
        $this->certificadoParticipanteId = null;
        $this->certificadoEmail = '';
        $this->resetValidation();
    }

    public function atualizarValor($id, $valor)
    {
        Validator::make(
            ['id' => $id, 'valor' => $valor],
            [
                'id' => ['required', 'exists:interlab_inscritos,id'],
                'valor' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            ],
            [
                'id.required' => 'O ID é obrigatório.',
                'id.exists' => 'Participante não encontrado.',
                'valor.required' => 'O valor é obrigatório.',
                'valor.numeric' => 'O valor deve ser numérico.',
                'valor.min' => 'O valor deve ser maior ou igual a zero.',
                'valor.max' => 'O valor máximo permitido é 999.999,99.',
            ]
        )->validate();

        $participante = InterlabInscrito::findOrFail($id);
        $participante->valor = $valor;
        $participante->save();

        if ($valor > 0) {
            app(GerarLancamentoInterlabAction::class)->execute($participante, $valor);
        }
    }

    public function render()
    {
        $intelabinscritos = InterlabInscrito::where('agenda_interlab_id', $this->idinterlab)
            ->with([
                'empresa:id,cpf_cnpj,nome_razao,associado',
                'pessoa:id,nome_razao,email',
                'laboratorio.endereco',
                'analistas',
            ])
            ->orderBy('data_inscricao', 'desc')
            ->get();

        // Agrupar inscritos por empresa mantendo a ordenação por data
        $inscritosPorEmpresa = $intelabinscritos->groupBy('empresa_id');

        // Ordenar empresas pela data de inscrição mais recente de seus laboratórios
        $empresaIds = $inscritosPorEmpresa->map(function ($inscritos) {
            return [
                'empresa_id' => $inscritos->first()->empresa_id,
                'data_mais_recente' => $inscritos->first()->data_inscricao,
            ];
        })->sortByDesc('data_mais_recente')->pluck('empresa_id');

        $interlabempresasinscritas = $this->empresasInscritasOrdenadas($inscritosPorEmpresa, $empresaIds);

        $participanteIds = $intelabinscritos->pluck('id')->all();
        $tagsSenhaDoc = $participanteIds === []
            ? collect()
            : DadosGeraDoc::query()
                ->where('tipo', 'tag_senha')
                ->whereIn('content->participante_id', $participanteIds)
                ->get()
                ->keyBy(fn ($doc) => $doc->content['participante_id'] ?? null);

        return view('livewire.interlab.list-participantes', [
            'intelabinscritos' => $intelabinscritos,
            'interlabempresasinscritas' => $interlabempresasinscritas,
            'inscritosPorEmpresa' => $inscritosPorEmpresa,
            'tagsSenhaDoc' => $tagsSenhaDoc,
        ]);
    }

    /**
     * Empresas já carregadas via eager load nos inscritos (evita filtrar milhares de Pessoa no PHP).
     *
     * @param  Collection<int, Collection<int, InterlabInscrito>>  $inscritosPorEmpresa
     * @param  Collection<int, int>  $empresaIds
     * @return Collection<int, \App\Models\Pessoa>
     */
    private function empresasInscritasOrdenadas(Collection $inscritosPorEmpresa, Collection $empresaIds): Collection
    {
        return $empresaIds
            ->map(fn ($empresaId) => $inscritosPorEmpresa->get($empresaId)?->first()?->empresa)
            ->filter()
            ->values();
    }
}

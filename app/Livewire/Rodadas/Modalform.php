<?php

namespace App\Livewire\Rodadas;

use App\Actions\LivewireFileUploadAction;
use App\Livewire\Forms\RodadaForm;
use App\Models\AgendaInterlab;
use App\Models\InterlabRodada;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Modalform extends Component
{
    use WithFileUploads;

    public AgendaInterlab $agendainterlab;
    public ?string $rodadaUid = null;
    public RodadaForm $form;

    // Propriedade separada para parâmetros (checkboxes não funcionam bem em Form Objects)
    public array $parametros = [];

    // Propriedades separadas para upload de arquivos (Livewire não funciona bem com uploads em Form Objects)
    #[Validate('nullable|file|mimes:doc,docx,pdf|max:5120', message: [
        'file' => 'O arquivo de envio de amostras deve ser válido',
        'mimes' => 'O arquivo de envio de amostras deve ser do tipo: doc, docx ou pdf',
        'max' => 'O arquivo de envio de amostras não pode ser maior que 5MB',
    ])]
    public $arquivo_envio;

    #[Validate('nullable|file|mimes:doc,docx,pdf|max:5120', message: [
        'file' => 'O arquivo de início de ensaios deve ser válido',
        'mimes' => 'O arquivo de início de ensaios deve ser do tipo: doc, docx ou pdf',
        'max' => 'O arquivo de início de ensaios não pode ser maior que 5MB',
    ])]
    public $arquivo_inicio_ensaios;

    #[Validate('nullable|file|mimes:doc,docx,pdf|max:5120', message: [
        'file' => 'O arquivo de limite de envio deve ser válido',
        'mimes' => 'O arquivo de limite de envio deve ser do tipo: doc, docx ou pdf',
        'max' => 'O arquivo de limite de envio não pode ser maior que 5MB',
    ])]
    public $arquivo_limite_envio_resultados;

    #[Validate('nullable|file|mimes:doc,docx,pdf|max:5120', message: [
        'file' => 'O arquivo de divulgação deve ser válido',
        'mimes' => 'O arquivo de divulgação deve ser do tipo: doc, docx ou pdf',
        'max' => 'O arquivo de divulgação não pode ser maior que 5MB',
    ])]
    public $arquivo_divulgacao_relatorios;

    public function mount(): void
    {
        if ($this->rodadaUid) {
            $rodada = InterlabRodada::where('uid', $this->rodadaUid)->first();
            $this->form->setRodada($rodada);
            $this->parametros = $rodada->parametros->pluck('parametro_id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->form->setNew($this->agendainterlab->id);
            $this->parametros = [];
        }
    }

    public function removerArquivo(string $nomeArquivo, string $campo): void
    {
        if (empty($nomeArquivo)) {
            return;
        }

        $caminhoArquivo = public_path('interlab-material/' . $nomeArquivo);
        if (file_exists($caminhoArquivo)) {
            unlink($caminhoArquivo);
        }

        if ($this->rodadaUid) {
            $interlab_rodada = InterlabRodada::where('uid', $this->rodadaUid)->first();
            if ($interlab_rodada) {
                $interlab_rodada->$campo = null;
                $interlab_rodada->save();
            }
        }
    }

    private function limparArquivos(): void
    {
        $this->arquivo_envio = null;
        $this->arquivo_inicio_ensaios = null;
        $this->arquivo_limite_envio_resultados = null;
        $this->arquivo_divulgacao_relatorios = null;
    }

    private function processarArquivo($arquivo, string $campo, InterlabRodada $interlab_rodada): void
    {
        if (!$arquivo) {
            return;
        }

        // Remove arquivo antigo se existir
        if ($interlab_rodada->$campo) {
            $caminhoArquivoAntigo = public_path('interlab-material/' . $interlab_rodada->$campo);
            if (file_exists($caminhoArquivoAntigo)) {
                unlink($caminhoArquivoAntigo);
            }
        }

        $nomeArquivo = LivewireFileUploadAction::handle($arquivo, 'interlab-material');
        if ($nomeArquivo) {
            $interlab_rodada->$campo = $nomeArquivo;
        }
    }

    public function salvar(): void
    {
        $this->validate();

        $isNew = empty($this->rodadaUid);

        $arquivos = [
            'arquivo_envio' => $this->arquivo_envio,
            'arquivo_inicio_ensaios' => $this->arquivo_inicio_ensaios,
            'arquivo_limite_envio_resultados' => $this->arquivo_limite_envio_resultados,
            'arquivo_divulgacao_relatorios' => $this->arquivo_divulgacao_relatorios,
        ];

        DB::transaction(function () use ($arquivos) {
            $interlab_rodada = InterlabRodada::updateOrCreate(
                ['uid' => $this->form->uid],
                $this->form->toArray()
            );

            foreach ($arquivos as $campo => $arquivo) {
                $this->processarArquivo($arquivo, $campo, $interlab_rodada);
            }

            $interlab_rodada->save();
            $interlab_rodada->updateParametros($this->parametros);
        });

        // Atualiza rodadaUid se for nova
        if ($isNew) {
            $this->rodadaUid = $this->form->uid;
        }

        // Recarrega os dados do form
        $rodada = InterlabRodada::where('uid', $this->form->uid)->first();
        if ($rodada) {
            $this->form->setRodada($rodada);
            $this->parametros = $rodada->parametros->pluck('parametro_id')->map(fn($id) => (string) $id)->toArray();
        }
        $this->limparArquivos();

        $mensagem = $isNew ? 'Rodada criada com sucesso!' : 'Rodada atualizada com sucesso!';
        $this->dispatch('refresh-rodadas-list');
        $this->dispatch('show-rodada-success', message: $mensagem);
    }

    /**
     * Retorna os arquivos salvos no banco de dados
     */
    public function getArquivosSalvosProperty(): array
    {
        if (empty($this->rodadaUid)) {
            return [
                'arquivo_envio' => null,
                'arquivo_inicio_ensaios' => null,
                'arquivo_limite_envio_resultados' => null,
                'arquivo_divulgacao_relatorios' => null,
            ];
        }

        $rodada = InterlabRodada::where('uid', $this->rodadaUid)->first();

        return [
            'arquivo_envio' => $rodada?->arquivo_envio,
            'arquivo_inicio_ensaios' => $rodada?->arquivo_inicio_ensaios,
            'arquivo_limite_envio_resultados' => $rodada?->arquivo_limite_envio_resultados,
            'arquivo_divulgacao_relatorios' => $rodada?->arquivo_divulgacao_relatorios,
        ];
    }

    public function render()
    {
        return view('livewire.rodadas.modalform', [
            'interlabParametros' => $this->agendainterlab->parametros,
        ]);
    }
}

<?php

namespace App\Livewire\Interlab;

use App\Actions\Financeiro\GerarLancamentoInterlabAction;
use App\Livewire\Forms\EditarInscritoForm;
use App\Models\Endereco;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\Pessoa;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarInscrito extends Component
{
    public EditarInscritoForm $form;

    public ?InterlabInscrito $inscrito = null;

    public ?int $inscritoId = null;

    public bool $carregando = false;

    public bool $requerAnalistas = false;

    /** @var list<array{id: int, cpf_cnpj: string|null, nome_razao: string|null}> */
    public array $pessoas = [];

    public ?string $novoResponsavelId = null;

    #[On('abrir-editar-inscrito')]
    public function abrir(int $id): void
    {
        $this->resetValidation();
        $this->novoResponsavelId = null;

        $this->inscrito = null;
        $this->pessoas = [];
        $this->requerAnalistas = false;
        $this->form->analistas = [];
        $this->inscritoId = $id;
        $this->carregando = true;

        $this->dispatch('offcanvas:open');
    }

    public function carregarInscrito(): void
    {
        if ($this->inscritoId === null) {
            return;
        }

        $solicitadoId = $this->inscritoId;

        try {
            $inscrito = InterlabInscrito::query()
                ->with(['empresa', 'pessoa', 'laboratorio.endereco', 'analistas', 'agendaInterlab.interlab'])
                ->findOrFail($solicitadoId);

            if ($this->inscritoId !== $solicitadoId) {
                return;
            }

            $this->inscrito = $inscrito;
            $this->requerAnalistas = ($inscrito->agendaInterlab?->interlab?->avaliacao ?? null) === 'ANALISTA';
            $this->form->setFromInscrito($this->inscrito);
            if ($this->requerAnalistas) {
                $this->form->setAnalistasFromInscrito($this->inscrito);
            } else {
                $this->form->analistas = [];
            }
            $this->pessoas = $this->pessoasFisicas((int) $this->inscrito->pessoa_id);

            $this->dispatch('editar-inscrito:carregado');
        } catch (ModelNotFoundException) {
            if ($this->inscritoId === $solicitadoId) {
                $this->dispatch('show-error-alert', message: 'Inscrição não encontrada.');
                $this->dispatch('offcanvas:close');
                $this->inscrito = null;
                $this->pessoas = [];
                $this->inscritoId = null;
                $this->carregando = false;
            }
        } finally {
            if ($this->inscritoId === $solicitadoId) {
                $this->carregando = false;
            }
        }
    }

    public function salvar(): void
    {
        if ($this->inscrito === null) {
            return;
        }

        $this->form->validate();

        if ($this->requerAnalistas && count($this->form->analistas) > 0) {
            $this->validate(
                $this->form->rulesAnalistas($this->inscrito->id),
                $this->form->messagesAnalistas()
            );
        }

        $lab = $this->inscrito->laboratorio;
        if ($lab === null || $this->inscrito->empresa_id === null) {
            $this->dispatch('show-error-alert', message: 'Inscrição sem laboratório ou empresa vinculada.');

            return;
        }

        $valorNumerico = (float) (formataMoeda($this->form->valor) ?? 0);

        DB::transaction(function () use ($lab): void {
            $this->inscrito->update($this->form->toInscritoArray());

            $lab->update($this->form->toLabArray());

            $enderecoData = $this->form->toEnderecoArray((int) $this->inscrito->empresa_id);

            if ($lab->endereco_id) {
                Endereco::query()->updateOrCreate(
                    ['id' => $lab->endereco_id],
                    $enderecoData
                );
            } else {
                $endereco = Endereco::query()->create($enderecoData);
                InterlabLaboratorio::query()->whereKey($lab->id)->update(['endereco_id' => $endereco->id]);
            }

            if ($this->requerAnalistas) {
                foreach ($this->form->analistas as $row) {
                    InterlabAnalista::query()
                        ->where('id', $row['id'])
                        ->where('interlab_inscrito_id', $this->inscrito->id)
                        ->update([
                            'nome' => $row['nome'],
                            'email' => $row['email'],
                            'telefone' => preg_replace('/\D/', '', $row['telefone'] ?? ''),
                        ]);
                }
            }
        });

        $this->inscrito->refresh()->load([
            'empresa',
            'pessoa',
            'laboratorio.endereco',
            'lancamentoFinanceiro',
            'analistas',
            'agendaInterlab.interlab',
        ]);

        if ($valorNumerico > 0) {
            app(GerarLancamentoInterlabAction::class)->execute($this->inscrito, $valorNumerico);
            $this->inscrito->refresh()->load([
                'empresa',
                'pessoa',
                'laboratorio.endereco',
                'lancamentoFinanceiro',
                'analistas',
                'agendaInterlab.interlab',
            ]);
        }
        $this->form->setFromInscrito($this->inscrito);
        if ($this->requerAnalistas) {
            $this->form->setAnalistasFromInscrito($this->inscrito);
        }

        $this->dispatch('offcanvas:close');
        $this->dispatch('refresh-interlab-participantes');
        $this->dispatch('show-success-alert', message: 'Dados salvos com sucesso.');
    }

    public function alterarResponsavel(?string $novoIdSelecionado = null): void
    {
        if ($this->inscrito === null) {
            return;
        }

        if ($novoIdSelecionado !== null && $novoIdSelecionado !== '') {
            $this->novoResponsavelId = $novoIdSelecionado;
        }

        $this->validate(
            [
                'novoResponsavelId' => [
                    'required',
                    'exists:pessoas,id',
                ],
            ],
            [
                'novoResponsavelId.required' => 'Selecione o novo responsável.',
                'novoResponsavelId.exists' => 'Responsável não encontrado.',
            ]
        );

        $novoId = (int) $this->novoResponsavelId;

        $isPf = Pessoa::query()
            ->whereKey($novoId)
            ->where('tipo_pessoa', 'PF')
            ->exists();

        if (! $isPf || $novoId === (int) $this->inscrito->pessoa_id) {
            $this->addError('novoResponsavelId', 'Selecione uma pessoa física válida.');

            return;
        }

        $this->inscrito->pessoa_id = $novoId;
        $this->inscrito->save();
        $this->inscrito->refresh()->load(['empresa', 'pessoa', 'laboratorio.endereco']);

        $this->pessoas = $this->pessoasFisicas((int) $this->inscrito->pessoa_id);

        $this->novoResponsavelId = null;

        $this->dispatch('offcanvas:close');
        $this->dispatch('refresh-interlab-participantes');
        $this->dispatch('show-success-alert', message: 'Responsável atualizado com sucesso.');
    }

    public function updatedFormCep(mixed $value): void
    {
        $cep = preg_replace('/\D/', '', (string) $value);

        if (strlen($cep) === 8) {
            $localEndereco = Endereco::query()
                ->where('cep', $cep)
                ->orderByDesc('id')
                ->first();

            if ($localEndereco) {
                $this->form->endereco = $localEndereco->endereco;
                $this->form->bairro = $localEndereco->bairro;
                $this->form->cidade = $localEndereco->cidade;
                $this->form->uf = $localEndereco->uf;
            } else {
                $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                if ($response->successful() && ! isset($response->json()['erro'])) {
                    $data = $response->json();
                    $this->form->endereco = $data['logradouro'] ?? '';
                    $this->form->bairro = $data['bairro'] ?? '';
                    $this->form->cidade = $data['localidade'] ?? '';
                    $this->form->uf = $data['uf'] ?? '';
                } else {
                    $this->addError('form.cep', 'CEP não encontrado.');
                }
            }
        } elseif (strlen($cep) > 0) {
            $this->addError('form.cep', 'CEP inválido. Certifique-se de que possui 8 dígitos.');
        }
    }

    public function render(): View
    {
        return view('livewire.interlab.editar-inscrito');
    }

    /**
     * @return list<array{id: int, cpf_cnpj: string|null, nome_razao: string|null}>
     */
    private function pessoasFisicas(int $excluirPessoaId): array
    {
        $rows = DB::select(
            'SELECT id, cpf_cnpj, nome_razao
             FROM pessoas
             WHERE id != ?
               AND tipo_pessoa = ?
               AND deleted_at IS NULL
             ORDER BY nome_razao ASC',
            [$excluirPessoaId, 'PF']
        );

        return array_map(
            static fn ($row) => [
                'id' => (int) $row->id,
                'cpf_cnpj' => $row->cpf_cnpj,
                'nome_razao' => $row->nome_razao,
            ],
            $rows
        );
    }
}

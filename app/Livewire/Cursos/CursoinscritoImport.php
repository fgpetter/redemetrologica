<?php

namespace App\Livewire\Cursos;

use App\Models\Pessoa;
use Livewire\Component;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Actions\CreateUserForPessoaAction;
use LaravelLegends\PtBrValidator\Rules\CpfOuCnpj;

class CursoinscritoImport extends Component
{
    use WithFileUploads;

    public $arquivo;
    public $preview = [];
    public $headers = [];
    public $rowErrors = [];
    public $agendacurso;

    public function mount(AgendaCursos $agendacurso)
    {
        $this->agendacurso = $agendacurso;
    }

    public function getHasErrorsProperty(): bool
    {
        return collect($this->rowErrors)->filter()->isNotEmpty() || $this->getErrorBag()->isNotEmpty();
    }

    public function updatedArquivo()
    {
        $this->reset(['preview', 'rowErrors', 'headers']);
        $this->resetErrorBag();

        $this->validate([
            'arquivo' => 'required|file|mimes:xls,xlsx,csv'
        ]);

        $collection = Excel::toCollection(null, $this->arquivo)->first();
        $this->headers = $collection->first()->map(fn($item) => strtolower(trim($item)))->toArray();

        $required = ['cpf_cnpj', 'nome_razao', 'email'];
        if (count(array_intersect($required, $this->headers)) !== count($required)) {
            $this->addError('arquivo', 'O arquivo não contém todas as colunas obrigatórias: cpf_cnpj, nome_razao, email.');
            return;
        }

        $data = $collection->slice(1)->map(function ($row) {
            return array_combine($this->headers, $row->toArray());
        })->values();


        if ($data->isEmpty()) {
            $this->addError('arquivo', 'A planilha não contém dados para importação.');
            return;
        }

        foreach ($data as $index => $item) {
            $this->preview[] = $item;
            $this->rowErrors[$index] = $this->validateRow($item);
        }
    }

    public function updatedPreview($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $row = $this->preview[$index];

        $this->rowErrors[$index] = $this->validateRow($row);
    }

    private function validateRow(array $item): ?string
    {
        $validator = Validator::make($item, [
            'cpf_cnpj' => ['required', new CpfOuCnpj],
            'nome_razao' => 'required|string|min:3',
            'email' => 'required|email',
        ], [
            'cpf_cnpj.required' => 'O CPF/CNPJ é obrigatório.',
            'cpf_cnpj.CpfOuCnpj' => 'O CPF/CNPJ informado é inválido.',
            'nome_razao.required' => 'O nome/razão social é obrigatório.',
            'nome_razao.min' => 'O nome/razão social deve ter no mínimo 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail informado é inválido.',
        ]);

        return $validator->fails() ? $validator->errors()->first() : null;
    }



    public function removeRow($index)
    {
        unset($this->preview[$index]);
        unset($this->rowErrors[$index]);
        $this->preview = array_values($this->preview);
        $this->rowErrors = array_values($this->rowErrors);
    }


    public function addRow()
    {
        $newRow = [
            'cpf_cnpj' => '',
            'nome_razao' => '',
            'email' => '',
            'telefone' => '',
        ];
        $this->preview[] = $newRow;
        $newIndex = count($this->preview) - 1;
        $this->rowErrors[$newIndex] = $this->validateRow($newRow);
    }

    public function importInscritos()
    {

        if ($this->hasErrors) {
            session()->flash('error', 'Não é possível importar enquanto houver erros nos registros.');
            return;
        }


        foreach ($this->preview as $item) {

            DB::transaction(function () use ($item) {
                // Remove caracteres especiais e espaços extras
                $item['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $item['cpf_cnpj']);
                $item['nome_razao'] = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($item['nome_razao']));
                $item['email'] = strtolower($item['email']);

                // Pula se o e-mail for inválido
                if (isInvalidEmail($item['email'])) {
                    return;
                }

                //Verifica se a pessoa já existe ou cria uma nova
                $pessoa = Pessoa::updateOrCreate(
                    ['cpf_cnpj' => $item['cpf_cnpj']],
                    [
                        'nome_razao' => $item['nome_razao'],
                        'email' => $item['email'],
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
            });

        }

       
        return redirect()
            ->route('agendamento-curso-in-company-insert', [$this->agendacurso->uid, '#participantes'])
            ->with('success', 'Inscrições importadas com sucesso!');
    }

    public function render()
    {
        return view('livewire.cursos.cursoinscrito-import');
    }
}

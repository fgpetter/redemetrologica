<?php

namespace App\Livewire\Cursos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\AgendaCursos;
use App\Models\Pessoa;
use App\Models\CursoInscrito;
use App\Actions\CreateUserForPessoaAction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
        $this->headers = $collection->first()->map(fn ($item) => strtolower(trim($item)))->toArray();

        $required = ['cpf_cnpj', 'nome_razao', 'email'];
        if (count(array_intersect($required, $this->headers)) !== count($required)) {
            $this->addError('arquivo', 'O arquivo não contém todas as colunas obrigatórias: cpf_cnpj, nome_razao, email.');
            return;
        }

        $data = $collection->slice(1)->map(function ($row) {
            return array_combine($this->headers, $row->toArray());
        })->values();

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
            'cpf_cnpj' => ['required', new \LaravelLegends\PtBrValidator\Rules\CpfOuCnpj],
            'nome_razao' => 'required|string|min:3',
            'email' => 'required|email',
        ]);

        return $validator->fails() ? $validator->errors()->first() : null;
    }

    public function importar()
    {
        DB::transaction(function () {
            foreach ($this->preview as $index => $item) {
                if ($this->rowErrors[$index]) {
                    continue;
                }

                $item['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $item['cpf_cnpj']);
                $item['nome_razao'] = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($item['nome_razao']));

                $pessoa = Pessoa::updateOrCreate(
                    ['cpf_cnpj' => $item['cpf_cnpj']],
                    [
                        'nome_razao' => $item['nome_razao'],
                        'email' => $item['email'],
                        'tipo_pessoa' => 'PF'
                    ]
                );

                CreateUserForPessoaAction::handle($pessoa);

                CursoInscrito::create([
                    'pessoa_id' => $pessoa->id,
                    'empresa_id' => $this->agendacurso->empresa_id,
                    'agenda_curso_id' => $this->agendacurso->id,
                    'data_inscricao' => now()
                ]);
            }
        });

        session()->flash('success', 'Inscrições importadas com sucesso!');
        return redirect(route('agendamento-curso-in-company-insert', $this->agendacurso->uid) . '#participantes');
    }

    public function render()
    {
        return view('livewire.cursos.cursoinscrito-import');
    }
}

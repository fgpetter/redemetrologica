<?php

namespace App\Actions;

use App\Models\Endereco;
use App\Models\Funcionario;
use App\Models\Pessoa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class SalvarFuncionarioAction
{
    /** @param array<string, mixed> $data */
    public function create(array $data): Funcionario
    {
        return $this->save($data, function (?string $curriculo) use ($data): Funcionario {
            $pessoa = Pessoa::create([
                'tipo_pessoa' => 'PF',
                'nome_razao' => ucfirst($data['nome_razao']),
                'cpf_cnpj' => $data['cpf_cnpj'],
                'rg_ie' => $data['rg_ie'] ?? null,
                'telefone' => $data['telefone'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            $endereco = Endereco::create($this->endereco($data) + ['pessoa_id' => $pessoa->id]);
            if ($data['end_padrao'] ?? false) {
                $pessoa->update(['end_padrao' => $endereco->id]);
            }

            return Funcionario::create($this->funcionario($data, $curriculo) + ['pessoa_id' => $pessoa->id]);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Funcionario $funcionario, array $data): void
    {
        $this->save($data, function (?string $curriculo) use ($funcionario, $data): Funcionario {
            $funcionario->update($this->funcionario($data, $curriculo));
            $funcionario->pessoa->update([
                'nome_razao' => ucfirst($data['nome_razao']),
                'cpf_cnpj' => $data['cpf_cnpj'],
                'rg_ie' => $data['rg_ie'] ?? null,
                'telefone' => $data['telefone'] ?? null,
                'email' => $data['email'] ?? null,
            ]);
            $endereco = $funcionario->pessoa->enderecos->first();
            $endereco->update($this->endereco($data));
            if ($data['end_padrao'] ?? false) {
                $funcionario->pessoa->update(['end_padrao' => $endereco->id]);
            }

            return $funcionario;
        });
    }

    /** @param array<string, mixed> $data */
    private function save(array $data, callable $persist): Funcionario
    {
        $curriculo = null;
        try {
            return DB::transaction(function () use ($data, $persist, &$curriculo): Funcionario {
                if (isset($data['curriculo'])) {
                    $curriculo = $this->upload($data['curriculo']);
                }

                return $persist($curriculo);
            });
        } catch (Throwable $exception) {
            if ($curriculo !== null) {
                File::delete(public_path($curriculo));
            }
            throw $exception;
        }
    }

    private function upload(UploadedFile $arquivo): string
    {
        $nome = sanitizeFileName(pathinfo($arquivo->getClientOriginalName(), PATHINFO_FILENAME));
        $nome .= '_'.uniqid('', true).'.'.$arquivo->getClientOriginalExtension();
        $arquivo->move(public_path('curriculos'), $nome);

        return 'curriculos/'.$nome;
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function endereco(array $data): array
    {
        return [
            'endereco' => $data['endereco'],
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cep' => $data['cep'],
            'cidade' => $data['cidade'],
            'uf' => $data['uf'],
        ];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function funcionario(array $data, ?string $curriculo): array
    {
        return [
            'cargo' => $data['cargo'] ?? null,
            'setor' => $data['setor'] ?? null,
            'admissao' => $data['admissao'],
            'demissao' => $data['demissao'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'curriculo' => $curriculo,
        ];
    }
}

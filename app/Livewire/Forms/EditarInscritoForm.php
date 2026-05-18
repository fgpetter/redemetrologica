<?php

namespace App\Livewire\Forms;

use App\Models\InterlabInscrito;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditarInscritoForm extends Form
{
    #[Validate('nullable|string|max:1000')]
    public ?string $informacoes_inscricao = null;

    #[Validate('nullable|string|max:255')]
    public ?string $responsavel_tecnico = null;

    #[Validate('nullable|string|max:20')]
    public ?string $telefone = null;

    #[Validate('nullable|email|max:255')]
    public ?string $email = null;

    #[Validate('nullable|string|max:255')]
    public ?string $tag_senha = null;

    #[Validate('nullable|string', message: ['string' => 'O valor digitado é inválido'])]
    public ?string $valor = null;

    #[Validate('required|string|max:255')]
    public string $labNome = '';

    #[Validate('required|string|max:9')]
    public string $cep = '';

    #[Validate('required|string|max:255')]
    public string $endereco = '';

    #[Validate('nullable|string|max:255')]
    public ?string $complemento = null;

    #[Validate('required|string|max:255')]
    public string $bairro = '';

    #[Validate('required|string|max:255')]
    public string $cidade = '';

    #[Validate('required|string|size:2')]
    public string $uf = '';

    public function setFromInscrito(InterlabInscrito $inscrito): void
    {
        $this->informacoes_inscricao = $inscrito->informacoes_inscricao;
        $this->responsavel_tecnico = $inscrito->responsavel_tecnico;
        $this->telefone = $inscrito->telefone;
        $this->email = $inscrito->email;
        $this->tag_senha = $inscrito->tag_senha;
        $this->valor = $inscrito->valor !== null
            ? number_format((float) $inscrito->valor, 2, ',', '')
            : null;

        $lab = $inscrito->laboratorio;
        $this->labNome = $lab?->nome ?? '';

        $endereco = $lab?->endereco;
        $this->cep = $endereco?->cep ?? '';
        $this->endereco = $endereco?->endereco ?? '';
        $this->complemento = $endereco?->complemento;
        $this->bairro = $endereco?->bairro ?? '';
        $this->cidade = $endereco?->cidade ?? '';
        $this->uf = $endereco?->uf ?? '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toInscritoArray(): array
    {
        return [
            'informacoes_inscricao' => $this->informacoes_inscricao,
            'responsavel_tecnico' => $this->responsavel_tecnico,
            'telefone' => (($digits = preg_replace('/\D/', '', (string) ($this->telefone ?? ''))) !== '') ? $digits : null,
            'email' => $this->email,
            'tag_senha' => $this->tag_senha,
            'valor' => formataMoeda($this->valor),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toLabArray(): array
    {
        return [
            'nome' => $this->labNome,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toEnderecoArray(int $pessoaId): array
    {
        $cepDigits = preg_replace('/\D/', '', $this->cep);

        return [
            'pessoa_id' => $pessoaId,
            'info' => 'Laboratório Interlab',
            'cep' => $cepDigits,
            'endereco' => $this->endereco,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'uf' => strtoupper($this->uf),
        ];
    }
}

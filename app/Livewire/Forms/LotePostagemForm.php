<?php

namespace App\Livewire\Forms;

use App\Enums\CorreiosFormatoObjeto;
use Closure;
use Livewire\Form;

class LotePostagemForm extends Form
{
    public string $nome = '';

    public ?string $altura = null;

    public ?string $largura = null;

    public ?string $comprimento = null;

    public string $pesoGramas = '';

    /** @var list<array{conteudo: string, quantidade: string, valor_unitario: string}> */
    public array $itensDeclaracao = [];

    public function setNew(): void
    {
        $this->reset();

        if (app()->isLocal()) {
            $this->nome = 'Amostras';
            $this->pesoGramas = '1000';
            $this->altura = '20';
            $this->largura = '40';
            $this->comprimento = '40';
            $this->itensDeclaracao = [
                [
                    'conteudo' => 'Amostras',
                    'quantidade' => '1',
                    'valor_unitario' => '1,00',
                ],
            ];

            return;
        }

        $this->itensDeclaracao = [
            $this->itemDeclaracaoVazio(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'altura' => ['required', 'string', 'regex:/^\d{1,3}$/'],
            'largura' => ['required', 'string', 'regex:/^\d{1,3}$/'],
            'comprimento' => ['required', 'string', 'regex:/^\d{1,3}$/'],
            'pesoGramas' => ['required', 'regex:/^[1-9]\d{0,5}$/'],
            'itensDeclaracao' => ['required', 'array', 'min:1'],
            'itensDeclaracao.*.conteudo' => ['required', 'string', 'min:5', 'max:60'],
            'itensDeclaracao.*.quantidade' => ['required', 'regex:/^[1-9]\d{0,10}$/'],
            'itensDeclaracao.*.valor_unitario' => ['required', 'string', $this->valorUnitarioValido()],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome do lote.',
            'altura.required' => 'Informe a altura da caixa, em centímetros.',
            'largura.required' => 'Informe a largura da caixa, em centímetros.',
            'comprimento.required' => 'Informe o comprimento da caixa, em centímetros.',
            'altura.regex' => 'A altura deve ter até 3 dígitos, em centímetros.',
            'largura.regex' => 'A largura deve ter até 3 dígitos, em centímetros.',
            'comprimento.regex' => 'O comprimento deve ter até 3 dígitos, em centímetros.',
            'pesoGramas.required' => 'Informe o peso em gramas.',
            'pesoGramas.regex' => 'O peso deve ser um número inteiro maior que zero, com até 6 dígitos.',
            'itensDeclaracao.required' => 'Informe ao menos um item da declaração.',
            'itensDeclaracao.min' => 'Informe ao menos um item da declaração.',
            'itensDeclaracao.*.conteudo.required' => 'Informe o conteúdo do item da declaração.',
            'itensDeclaracao.*.conteudo.min' => 'O conteúdo deve ter no mínimo 5 caracteres.',
            'itensDeclaracao.*.conteudo.max' => 'O conteúdo deve ter no máximo 60 caracteres.',
            'itensDeclaracao.*.quantidade.required' => 'Informe a quantidade do item.',
            'itensDeclaracao.*.quantidade.regex' => 'A quantidade deve ser um inteiro maior que zero, com até 11 dígitos.',
            'itensDeclaracao.*.valor_unitario.required' => 'Informe o valor unitário do item.',
        ];
    }

    /**
     * @return array{
     *     nome: string,
     *     codigo_formato: string,
     *     altura: ?string,
     *     largura: ?string,
     *     comprimento: ?string,
     *     diametro: ?string,
     *     peso_gramas: int,
     *     declaracao_conteudo: list<array{conteudo: string, quantidade: string, valor: string}>
     * }
     */
    public function toParametros(): array
    {
        return [
            'nome' => $this->nome,
            'codigo_formato' => CorreiosFormatoObjeto::CaixaPacote->value,
            'altura' => $this->altura,
            'largura' => $this->largura,
            'comprimento' => $this->comprimento,
            'diametro' => null,
            'peso_gramas' => (int) $this->pesoGramas,
            'declaracao_conteudo' => array_map(
                fn (array $item): array => [
                    'conteudo' => $item['conteudo'],
                    'quantidade' => $item['quantidade'],
                    'valor' => number_format((float) formataMoeda($item['valor_unitario']), 2, '.', ''),
                ],
                $this->itensDeclaracao,
            ),
        ];
    }

    /**
     * @return array{conteudo: string, quantidade: string, valor_unitario: string}
     */
    private function itemDeclaracaoVazio(): array
    {
        return [
            'conteudo' => '',
            'quantidade' => '',
            'valor_unitario' => '',
        ];
    }

    private function valorUnitarioValido(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            $decimal = formataMoeda($value);

            if ($decimal === null || ! is_numeric($decimal) || (float) $decimal <= 0) {
                $fail('Informe um valor unitário maior que zero.');
            }
        };
    }
}

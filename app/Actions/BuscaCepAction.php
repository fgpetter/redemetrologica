<?php

namespace App\Actions;

use App\Models\Endereco;
use Illuminate\Support\Facades\Http;

class BuscaCepAction
{
    /**
     * Busca o endereço pelo CEP.
     * Prioriza o banco de dados local, se não encontrar, busca na API ViaCEP.
     *
     * @param string $cep
     * @return array|null Retorna um array com os dados do endereço ou null se não encontrar/inválido.
     */
    public function execute(string $cep): ?array
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return null;
        }

        // Tenta buscar no banco de dados local primeiro
        $localEndereco = Endereco::where('cep', $cep)
            ->orderBy('id', 'desc')
            ->first();

        if ($localEndereco) {
            return [
                'cep' => $localEndereco->cep,
                'endereco' => $localEndereco->endereco,
                'bairro' => $localEndereco->bairro,
                'cidade' => $localEndereco->cidade,
                'uf' => $localEndereco->uf,
                'complemento' => $localEndereco->complemento,
            ];
        }

        // Se não encontrar, busca na API ViaCEP
        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if ($response->successful() && !isset($response->json()['erro'])) {
            $data = $response->json();
            return [
                'cep' => $cep,
                'endereco' => $data['logradouro'] ?? '',
                'bairro' => $data['bairro'] ?? '',
                'cidade' => $data['localidade'] ?? '',
                'uf' => $data['uf'] ?? '',
                'complemento' => '',
            ];
        }

        return null;
    }
}

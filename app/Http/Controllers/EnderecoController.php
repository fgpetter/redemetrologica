<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnderecoRequest;
use App\Models\Endereco;
use App\Models\InterlabLaboratorio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnderecoController extends Controller
{
    /**
     * Adiciona endereco
     *
     **/
    public function create(EnderecoRequest $request): RedirectResponse
    {

        $prepared_data = $request->validated();

        $endereco = Endereco::create([
            'pessoa_id' => $prepared_data['pessoa_id'],
            'info' => $prepared_data['info'],
            'cep' => $prepared_data['cep'],
            'endereco' => $prepared_data['endereco'],
            'complemento' => $prepared_data['complemento'],
            'cidade' => $prepared_data['cidade'],
            'uf' => $prepared_data['uf'],
        ]);

        if (! $endereco) {
            return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        if (isset($request->end_padrao)) {
            $endereco->pessoa->update(['end_padrao' => $endereco->id]);
        }

        return redirect()->back()->with('success', 'Endereço cadastrado com sucesso');
    }

    /**
     * Edita dados de endereco
     *
     **/
    public function update(EnderecoRequest $request, Endereco $endereco): RedirectResponse
    {

        $prepared_data = $request->validated();
        $endereco->update([
            'pessoa_id' => $prepared_data['pessoa_id'],
            'info' => $prepared_data['info'],
            'cep' => $prepared_data['cep'],
            'endereco' => $prepared_data['endereco'],
            'complemento' => $prepared_data['complemento'],
            'cidade' => $prepared_data['cidade'],
            'uf' => $prepared_data['uf'],
        ]);

        if (isset($request->end_padrao)) {
            $endereco->pessoa->update(['end_padrao' => $endereco->id]);
        } elseif ($endereco->pessoa->end_padrao == $endereco->id) {
            $endereco->pessoa->update(['end_padrao' => null]);
        }

        return redirect()->back()->with('success', 'Endereço atualizado');
    }

    /**
     * Remove endereço
     *
     * @param  Endereco  $user
     **/
    public function delete(Endereco $endereco, InterlabLaboratorio $laboratorio): RedirectResponse
    {

        // Verifica se o endereço está vinculado a algum PEP e impede a remoção
        if ($laboratorio->where('endereco_id', $endereco->id)->exists()) {
            return redirect()->back()->with('error', 'Não é possível remover este endereço, pois o mesmo está vinculado a um PEP.');
        }
        $endereco->pessoa->update(['end_padrao' => null]);
        $endereco->delete();

        return redirect()->back()->with('warning', 'Endereco removido');
    }

    public function check(Request $request): JsonResponse
    {
        $endereco = Endereco::select(['endereco', 'bairro', 'cidade', 'uf'])
            ->where(['cep' => $request->cep])
            ->firstOr(fn () => ['error' => 'No results']);

        return response()->json($endereco);
    }
}

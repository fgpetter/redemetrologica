<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PepAgendaListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nome_interlab' => $this->interlab?->nome,
            'uid' => $this->uid,
            'status' => strtolower((string) $this->status),
            'ano_referencia' => $this->ano_referencia,
        ];
    }
}

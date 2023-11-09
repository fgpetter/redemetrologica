<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    use HasFactory;

    /**
     * Lista os endereços de uma pessoa.
     */
    public function enderecos(): HasMany
    {
        return $this->hasMany(Endereco::class);
    }
}

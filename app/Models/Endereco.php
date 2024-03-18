<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Endereco extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Retorna pessoa do endereço.
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    protected function cep(): Attribute
    {
        return Attribute::make(
            // formatando o CEP para o formato 00000-000
            get: fn (string|null $value) => preg_replace("/([0-9]{5})([0-9]{3})/", "\$1-\$2", $value),
            set: fn (string|null $value) => preg_replace("/[^\d]/", "", $value),
        );
    }

}

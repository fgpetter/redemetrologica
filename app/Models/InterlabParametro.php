<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterlabParametro extends Model
{
    use HasFactory;

    protected $table = 'interlab_parametros';

    protected $guarded = [];

    /**
     * Carrega Parametro associado
     * @return BelongsTo
     */
    public function parametro(): BelongsTo
    {
        return $this->belongsTo(Parametro::class);
    }
    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Qualificacao extends Model
{
    use HasFactory;

    protected $table = 'qualificacoes';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Carrega avalidor
     * @return BelongsTo
     */
    public function avaliador() : BelongsTo
    {
        return $this->belongsTo(Avaliador::class);
    }


}

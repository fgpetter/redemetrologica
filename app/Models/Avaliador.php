<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Avaliador extends Model
{
    use HasFactory;

    protected $table = 'avaliadores';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Carrega pessoa
     * @return BelongsTo
     */
    public function pessoa() : BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }


}

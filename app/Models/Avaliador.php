<?php

namespace App\Models;


use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    // protected $casts = [
    //     'data_ingresso'  => 'date',
    // ];

    protected function dataIngresso(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('d/m/Y', strtotime($value)),
        );
    }

    /**
     * Carrega pessoa
     * @return BelongsTo
     */
    public function pessoa() : BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }


}

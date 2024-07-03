<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvaliadorArea extends Model
{
    use HasFactory;

    protected $table = 'avaliador_areas';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function avaliador()
    {
        return $this->belongsTo(Avaliador::class);
    }

    public function area()
    {
        return $this->belongsTo(AreaAtuacao::class, 'area_id', 'id');
    }

}

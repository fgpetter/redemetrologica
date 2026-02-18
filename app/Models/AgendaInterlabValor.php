<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaInterlabValor extends Model
{
    use HasFactory, SetDefaultUid;

    protected $table = 'agendainterlab_valores';

    protected $guarded = [];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_assoc' => 'decimal:2',
    ];

    public function agendaInterlab()
    {
        return $this->belongsTo(AgendaInterlab::class);
    }
}

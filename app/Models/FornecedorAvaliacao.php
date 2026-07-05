<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FornecedorAvaliacao extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'fornecedores_avaliacao';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * @return BelongsTo<AgendaInterlab, $this>
     */
    public function agendaInterlab(): BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }

    /**
     * @return BelongsTo<Fornecedor, $this>
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public static function calcularMedia(int $custo, int $tempo, int $qualidade): float
    {
        return round(($custo + $tempo + $qualidade) / 3, 2);
    }
}

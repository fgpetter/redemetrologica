<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class FornecedorAvaliacao extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'fornecedores_avaliacao';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }

    /**
     * @return BelongsTo<InterlabDespesaLancamento, $this>
     */
    public function lancamento(): BelongsTo
    {
        return $this->belongsTo(InterlabDespesaLancamento::class, 'interlab_despesa_lancamento_id');
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

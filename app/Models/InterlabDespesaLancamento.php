<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterlabDespesaLancamento extends Model
{
    use LogsActivity, SetDefaultUid, SoftDeletes;

    protected $table = 'interlab_despesa_lancamentos';

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

    /**
     * @return HasMany<InterlabDespesa, $this>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(InterlabDespesa::class, 'interlab_despesa_lancamento_id');
    }

    /**
     * @return HasOne<FornecedorAvaliacao, $this>
     */
    public function avaliacao(): HasOne
    {
        return $this->hasOne(FornecedorAvaliacao::class, 'interlab_despesa_lancamento_id');
    }
}

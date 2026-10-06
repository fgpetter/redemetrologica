<?php

namespace App\Models;

use App\Enums\CorreiosFormatoObjeto;
use App\Enums\InterlabLotePostagemStatus;
use App\Traits\SetDefaultUid;
use Database\Factories\InterlabLotePostagemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterlabLotePostagem extends Model
{
    /** @use HasFactory<InterlabLotePostagemFactory> */
    use HasFactory;

    use LogsActivity;
    use SetDefaultUid;

    protected $table = 'interlab_lote_postagens';

    protected $guarded = [];

    protected $casts = [
        'codigo_formato' => CorreiosFormatoObjeto::class,
        'peso_gramas' => 'integer',
        'declaracao_conteudo' => 'array',
        'status' => InterlabLotePostagemStatus::class,
        'ultima_progressao_em' => 'datetime',
        'documentos_enviados_em' => 'datetime',
    ];

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
     * @return HasMany<InterlabLotePostagemItem, $this>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(InterlabLotePostagemItem::class);
    }

    /**
     * @return HasMany<InterlabLotePostagemEtiqueta, $this>
     */
    public function etiquetas(): HasMany
    {
        return $this->hasMany(InterlabLotePostagemEtiqueta::class);
    }

    /**
     * @param  Builder<InterlabLotePostagem>  $query
     * @return Builder<InterlabLotePostagem>
     */
    public function scopeEmProcessamento(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            InterlabLotePostagemStatus::Concluido->value,
            InterlabLotePostagemStatus::Erro->value,
        ]);
    }
}

<?php

namespace App\Models;

use App\Enums\InterlabLotePostagemEtiquetaStatus;
use App\Traits\SetDefaultUid;
use Database\Factories\InterlabLotePostagemEtiquetaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterlabLotePostagemEtiqueta extends Model
{
    /** @use HasFactory<InterlabLotePostagemEtiquetaFactory> */
    use HasFactory;

    use LogsActivity;
    use SetDefaultUid;

    protected $table = 'interlab_lote_postagem_etiquetas';

    protected $guarded = [];

    protected $casts = [
        'solicitacao_iniciada_em' => 'datetime',
        'status' => InterlabLotePostagemEtiquetaStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * @return BelongsTo<InterlabLotePostagem, $this>
     */
    public function lotePostagem(): BelongsTo
    {
        return $this->belongsTo(InterlabLotePostagem::class, 'interlab_lote_postagem_id');
    }

    /**
     * @return HasMany<InterlabLotePostagemItem, $this>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(InterlabLotePostagemItem::class, 'interlab_lote_postagem_etiqueta_id');
    }
}

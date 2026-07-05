<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterlabDespesa extends Model
{
    use LogsActivity, SetDefaultUid, SoftDeletes;

    protected $table = 'interlab_despesas';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * Carrega Interlab
     */
    public function interlab(): BelongsTo
    {
        return $this->belongsTo(Interlab::class);
    }

    /**
     * Carrega fornecedor relacionado
     */
    public function interlabFornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    /**
     * Carrega material padrão
     */
    public function materialPadrao(): HasOne
    {
        return $this->hasOne(MaterialPadrao::class, 'id', 'material_padrao_id');
    }

    protected $casts = [
        'validade' => 'date',
        'data_compra' => 'date',
    ];
}

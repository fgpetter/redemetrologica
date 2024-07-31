<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterlabDespesa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'interlab_despesas';

    protected $guarded = [];

    /**
     * Carrega Interlab
     * @return BelongsTo
     */
    public function interlab(): BelongsTo
    {
        return $this->belongsTo(Interlab::class);
    }

    /**
     * Carrega material padrão
     * @return HasOne
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

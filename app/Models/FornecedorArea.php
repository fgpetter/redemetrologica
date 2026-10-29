<?php

namespace App\Models;

use App\Casts\TelefoneCast;
use App\Enums\FornecedorArea as FornecedorAreaEnum;
use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class FornecedorArea extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $fillable = [
        'fornecedor_id', 'area', 'atuacao',
        'pessoa_contato', 'pessoa_contato_email', 'pessoa_contato_telefone',
    ];

    protected $table = 'fornecedores_areas';

    protected function casts(): array
    {
        return [
            'area' => FornecedorAreaEnum::class,
            'pessoa_contato_telefone' => TelefoneCast::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }
}

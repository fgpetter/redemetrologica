<?php

namespace App\Models;

use App\Enums\FornecedorArea as FornecedorAreaEnum;
use App\Models\Fornecedor;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FornecedorArea extends Model
{
    use SetDefaultUid, LogsActivity;

    protected $fillable = [
        'fornecedor_id', 'area', 'atuacao',
        'pessoa_contato', 'pessoa_contato_email', 'pessoa_contato_telefone',
    ];

    protected $table = 'fornecedores_areas';

    protected function casts(): array
    {
        return [
            'area' => FornecedorAreaEnum::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }
}

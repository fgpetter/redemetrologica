<?php

namespace App\Models;

use App\Casts\TelefoneCast;
use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;

class NotaFiscalAvulsa extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'notas_fiscais_avulsas';

    protected $guarded = [];

    protected $casts = [
        'telefone' => TelefoneCast::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('Usuários');
    }
}

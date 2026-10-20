<?php

namespace App\Models;

use App\Casts\TelefoneCast;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NotaFiscalAvulsa extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'notas_fiscais_avulsas';

    protected $guarded = [];

    protected $casts = [
        'telefone' => TelefoneCast::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Usuários');
    }
}

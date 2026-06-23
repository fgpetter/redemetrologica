<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CentroCusto extends Model
{
    use LogsActivity, SetDefaultUid, SoftDeletes;

    protected $table = 'centro_custos';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /** ID do centro de custo TREINAMENTO (inscrições em curso). */
    public const ID_TREINAMENTO = 3;

    /** ID do centro de custo INTERLABORATORIAL (inscrições em interlab). */
    public const ID_INTERLABORATORIAL = 4;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }
}

<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use App\Models\LaboratorioInterno;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;


class AreaAtuacao extends Model
{
    use LogsActivity, SetDefaultUid;
    protected $table = 'areas_atuacao';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * Define a relação com os laboratórios internos.
     */
    public function laboratoriosInternos()
    {
        return $this->hasMany(LaboratorioInterno::class, 'area_atuacao_id', 'id');
    }
}

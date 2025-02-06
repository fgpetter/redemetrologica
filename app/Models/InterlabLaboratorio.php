<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;



class InterlabLaboratorio extends Model
{

    use LogsActivity, SetDefaultUid;

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'interlab_laboratorios';


    public function getActivitylogOptions(): LogOptions
    {
      return LogOptions::defaults()
      ->logOnly(['*'])
      ->useLogName( get_class($this) );
    }

    /**
     * Carrega Empresa associado
     * @return HasOne
     */
    public function empresa(): HasOne
    {
      return $this->hasOne(Pessoa::class, 'id', 'empresa_id');
    }

    public function endereco(): HasOne
    {
      return $this->hasOne(Endereco::class, 'id', 'endereco_id');
    }


}

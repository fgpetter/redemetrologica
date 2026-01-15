<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Casts\Attribute;


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

    protected function telefone(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $this->formataTel($value),
            set: fn (string|null $value) => preg_replace("/[^\d]/", "", $value),
        );
    }

    protected function formataTel(string|null $value): string
    {
        if(strlen($value) === 11){
            return preg_replace("/([0-9]{2})([0-9]{5})([0-9]{4})/", "(\$1) \$2-\$3", $value);
        }
        if(strlen($value) === 10){
            return preg_replace("/([0-9]{2})([0-9]{4})([0-9]{4})/", "(\$1) \$2-\$3", $value);
        }

        return '';

    }



}

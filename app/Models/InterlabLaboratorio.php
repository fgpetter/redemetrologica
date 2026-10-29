<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Support\LogOptions;

class InterlabLaboratorio extends Model
{
    use LogsModelActivity, SetDefaultUid;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'interlab_laboratorios';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }

    /**
     * Carrega Empresa associado
     */
    public function empresa(): HasOne
    {
        return $this->hasOne(Pessoa::class, 'id', 'empresa_id');
    }

    public function endereco(): HasOne
    {
        return $this->hasOne(Endereco::class, 'id', 'endereco_id');
    }

    /**
     * Retorna inscrições do laboratório em PEPs
     */
    public function inscritos(): HasMany
    {
        return $this->hasMany(InterlabInscrito::class, 'laboratorio_id', 'id');
    }

    /**
     * Retorna analistas vinculados ao laboratório
     *
     * @return HasMany
     */
    public function analistas(): HasManyThrough
    {
        return $this->hasManyThrough(
            InterlabAnalista::class,
            InterlabInscrito::class,
            'laboratorio_id',
            'interlab_inscrito_id',
            'id',
            'id',
        );
    }

    protected function telefone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->formataTel($value),
            set: fn (?string $value) => preg_replace("/[^\d]/", '', $value),
        );
    }

    protected function formataTel(?string $value): string
    {
        if (strlen($value) === 11) {
            return preg_replace('/([0-9]{2})([0-9]{5})([0-9]{4})/', '($1) $2-$3', $value);
        }
        if (strlen($value) === 10) {
            return preg_replace('/([0-9]{2})([0-9]{4})([0-9]{4})/', '($1) $2-$3', $value);
        }

        return '';

    }
}

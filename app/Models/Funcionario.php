<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;


class Funcionario extends Model
{
    use LogsActivity, SetDefaultUid;

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
     * Carrega pessoa
     * @return BelongsToMany
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }
}

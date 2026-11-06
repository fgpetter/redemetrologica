<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;

class Permission extends Model
{
    // A tabela de permissões não possui UID nem é exposta por identificador público.
    use LogsModelActivity;

    protected $table = 'permissions';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }
}

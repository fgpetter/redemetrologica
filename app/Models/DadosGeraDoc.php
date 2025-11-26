<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\SetDefaultUid;

class DadosGeraDoc extends Model
{
    use SetDefaultUid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dados_gera_doc';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'content' => 'array',
    ];

    /**
     * Boot the model and generate unique link UUID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->link)) {
                $model->link = (string) Str::uuid();
            }
        });
    }
}

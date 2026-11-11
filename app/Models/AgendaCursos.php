<?php

namespace App\Models;

use App\Casts\TelefoneCast;
use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Support\LogOptions;

class AgendaCursos extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'agenda_cursos';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'validade_proposta' => 'date',
        'contato_telefone' => TelefoneCast::class,
    ];

    protected static function booted(): void
    {
        static::updating(function (AgendaCursos $agenda) {
            if ($agenda->isDirty('data_inicio')) {
                $agenda->conferencia_enviada_em = null;
                $agenda->pendencias_enviadas_em = null;
                $agenda->inscritos()->update(['informacoes_enviadas_em' => null]);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }

    /**
     * Carrega curso
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Carrega custos atrelados
     */
    public function despesas(): HasMany
    {
        return $this->hasMany(CursoDespesa::class);
    }

    public function instrutor(): BelongsTo
    {
        return $this->belongsTo(Instrutor::class)->withTrashed();
    }

    public function inscritos(): HasMany
    {
        return $this->hasMany(CursoInscrito::class, 'agenda_curso_id', 'id');
    }

    public function materiaisTreinamento(): HasMany
    {
        return $this->hasMany(AgendaCursoArquivo::class, 'agenda_curso_id');
    }
}

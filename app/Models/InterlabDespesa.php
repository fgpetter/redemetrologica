<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterlabDespesa extends Model
{
    use LogsActivity, SetDefaultUid, SoftDeletes;

    protected $table = 'interlab_despesas';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * @return BelongsTo<InterlabDespesaLancamento, $this>
     */
    public function lancamento(): BelongsTo
    {
        return $this->belongsTo(InterlabDespesaLancamento::class, 'interlab_despesa_lancamento_id');
    }

    protected function casts(): array
    {
        return [
            'validade' => 'date',
            'data_compra' => 'date',
        ];
    }
}

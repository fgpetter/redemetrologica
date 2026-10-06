<?php

namespace App\Models;

use App\Enums\CorreiosPrePostagemStatus;
use App\Enums\InterlabConsultaPrazoStatusVisual;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Traits\SetDefaultUid;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterlabLotePostagemItem extends Model
{
    /** @use HasFactory<InterlabLotePostagemItemFactory> */
    use HasFactory;

    use LogsActivity;
    use SetDefaultUid;

    protected $table = 'interlab_lote_postagem_itens';

    protected $guarded = [];

    protected $casts = [
        'status_atual' => CorreiosPrePostagemStatus::class,
        'consulta_prazo_request' => 'array',
        'consulta_prazo_response' => 'array',
        'consulta_prazo_em' => 'datetime',
        'destinatario' => 'array',
        'status' => InterlabLotePostagemItemStatus::class,
        'rastreio_payload' => 'array',
        'rastreado_em' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * @return BelongsTo<InterlabLotePostagem, $this>
     */
    public function lotePostagem(): BelongsTo
    {
        return $this->belongsTo(InterlabLotePostagem::class, 'interlab_lote_postagem_id');
    }

    /**
     * @return BelongsTo<InterlabInscrito, $this>
     */
    public function inscrito(): BelongsTo
    {
        return $this->belongsTo(InterlabInscrito::class, 'interlab_inscrito_id');
    }

    /**
     * @return BelongsTo<InterlabLaboratorio, $this>
     */
    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(InterlabLaboratorio::class, 'interlab_laboratorio_id');
    }

    /**
     * @return BelongsTo<InterlabLotePostagemEtiqueta, $this>
     */
    public function etiqueta(): BelongsTo
    {
        return $this->belongsTo(InterlabLotePostagemEtiqueta::class, 'interlab_lote_postagem_etiqueta_id');
    }

    public function statusVisualClassificacao(): InterlabConsultaPrazoStatusVisual
    {
        if ($this->status === InterlabLotePostagemItemStatus::Entregue) {
            return InterlabConsultaPrazoStatusVisual::Entregue;
        }

        $lote = $this->relationLoaded('lotePostagem')
            ? $this->lotePostagem
            : $this->lotePostagem()->first();

        if ($lote?->status === InterlabLotePostagemStatus::Erro
            && $this->status !== InterlabLotePostagemItemStatus::EtiquetaGerada) {
            return InterlabConsultaPrazoStatusVisual::Intervencao;
        }

        if ($lote?->status === InterlabLotePostagemStatus::GerandoDeclaracao) {
            return InterlabConsultaPrazoStatusVisual::GerandoDeclaracao;
        }

        if ($this->status === InterlabLotePostagemItemStatus::EtiquetaGerada) {
            return InterlabConsultaPrazoStatusVisual::EtiquetaGerada;
        }

        if ($lote?->status === InterlabLotePostagemStatus::GerandoEtiquetas
            && $this->status === InterlabLotePostagemItemStatus::Prepostado) {
            return InterlabConsultaPrazoStatusVisual::GerandoEtiqueta;
        }

        if ($this->status === InterlabLotePostagemItemStatus::Prepostado) {
            return InterlabConsultaPrazoStatusVisual::Prepostado;
        }

        if ($lote?->status === InterlabLotePostagemStatus::GerandoPrePostagens
            && $this->status === InterlabLotePostagemItemStatus::Classificado) {
            return InterlabConsultaPrazoStatusVisual::CriandoPrePostagem;
        }

        if ($this->status === InterlabLotePostagemItemStatus::Classificado) {
            return $this->codigo_servico === (string) config('services.correios.codigo_servico_sedex')
                ? InterlabConsultaPrazoStatusVisual::Sedex
                : InterlabConsultaPrazoStatusVisual::Sedex12;
        }

        if ($this->consulta_prazo_request === null) {
            return InterlabConsultaPrazoStatusVisual::AguardandoConsulta;
        }

        if ($this->consulta_prazo_response === null) {
            return InterlabConsultaPrazoStatusVisual::Consultando;
        }

        return InterlabConsultaPrazoStatusVisual::AguardandoTentativa;
    }

    public function detalheProximaTentativa(): ?string
    {
        if ($this->statusVisualClassificacao() !== InterlabConsultaPrazoStatusVisual::AguardandoTentativa) {
            return null;
        }

        $tentativa = data_get($this->consulta_prazo_response, 'tentativa');
        $quando = data_get($this->consulta_prazo_response, 'proxima_tentativa_em');
        $partes = [];

        if (is_numeric($tentativa)) {
            $partes[] = 'tentativa '.$tentativa;
        }

        if (is_string($quando) && $quando !== '') {
            $partes[] = 'próxima tentativa em '.Carbon::parse($quando)
                ->timezone('America/Sao_Paulo')
                ->format('d/m/Y H:i');
        }

        return $partes === [] ? null : implode(', ', $partes);
    }
}

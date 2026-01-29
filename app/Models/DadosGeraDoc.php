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

    /**
     * Retorna o nome do arquivo padrão com base no tipo
     */
    public function getFileNameAttribute(): string
    {
        if ($this->tipo === 'tag_senha') {
            $nameSlug = Str::slug($this->content['laboratorio_nome'] ?? 'tag');
            return "tag_senha_{$nameSlug}_{$this->link}.pdf";
        }

        if ($this->tipo === 'certificado') {
            $nameSlug = Str::slug($this->content['participante_nome'] ?? 'certificado');
            return "certificado_{$nameSlug}_{$this->link}.pdf";
        }

        return "documento_{$this->link}.pdf";
    }

    /**
     * Retorna o caminho de armazenamento padrão
     */
    public function getStoragePathAttribute(): string
    {
        $folder = $this->tipo === 'tag_senha' ? 'senhas' : 'certificados';
        return "public/docs/{$folder}/{$this->file_name}";
    }
}

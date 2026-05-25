<?php

namespace App\Actions;

use App\Models\AgendaInterlab;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;

class GerarTagSenhaInterlabAction
{
    public const TIPO_LABORATORIO = 'interlab_laboratorios';

    public const TIPO_ANALISTA = 'interlab_analistas';

    public function execute(AgendaInterlab $agendaInterlab, string $tipo): string
    {
        $tag = $agendaInterlab->interlab->tag ?? throw new \Exception('Tag do interlab não encontrada');
        $senha = $tag.rand(111, 999);

        if ($tipo === self::TIPO_LABORATORIO) {
            while (InterlabInscrito::where('tag_senha', $senha)->where('agenda_interlab_id', $agendaInterlab->id)->exists()) {
                $senha = $tag.rand(111, 999);
            }
        }

        if ($tipo === self::TIPO_ANALISTA) {
            while (InterlabAnalista::where('tag_senha', $senha)->exists()) {
                $senha = $tag.rand(1111, 9999);
            }
        }

        return $senha;
    }
}

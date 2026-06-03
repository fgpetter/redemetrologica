<?php

namespace Tests\Feature;

use App\Models\Convite;
use Tests\TestCase;

class ConviteActivityTest extends TestCase
{
    /**
     * Convites é modelo legado — tabela foi removida pela migration
     * 2026_01_21_010034_drop_convites_table.
     *
     * O modelo ainda existe no código e o plano BACKENDREFAC prevê
     * adicionar o trait LogsActivity para consistência.
     *
     * Testes verificam apenas a estrutura da classe, sem acesso ao banco.
     */
    public function test_model_convite_possui_metodo_get_activitylog_options(): void
    {
        $convite = new Convite;

        $this->assertTrue(method_exists($convite, 'getActivitylogOptions'));
    }
}

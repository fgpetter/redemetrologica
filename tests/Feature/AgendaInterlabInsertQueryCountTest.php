<?php

namespace Tests\Feature;

use App\Models\AgendaInterlab;
use App\Models\Interlab;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AgendaInterlabInsertQueryCountTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioFuncionario(): User
    {
        $user = User::factory()->create();

        $permission = Permission::withoutEvents(function (): Permission {
            return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
        });

        $user->permissions()->syncWithoutDetaching([$permission->id]);

        return $user;
    }

    public function test_insert_edit_page_keeps_query_count_under_budget(): void
    {
        $interlab = Interlab::query()->create([
            'nome' => 'Interlab teste queries',
            'tipo' => 'INTERLABORATORIAL',
        ]);

        $agenda = AgendaInterlab::query()->create([
            'interlab_id' => $interlab->id,
            'status' => 'CONFIRMADO',
            'certificado' => 'EMPRESA',
            'inscricao' => true,
            'ano_referencia' => (int) now()->format('Y'),
        ]);

        DB::enableQueryLog();

        $response = $this->actingAs($this->usuarioFuncionario())
            ->get(route('agenda-interlab-insert', $agenda->uid));

        $response->assertOk();

        $queryCount = count(DB::getQueryLog());

        $this->assertLessThan(
            40,
            $queryCount,
            "A página insert do agenda-interlab executou {$queryCount} queries (limite: 40)."
        );
    }
}

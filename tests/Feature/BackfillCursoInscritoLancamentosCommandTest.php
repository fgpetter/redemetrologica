<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackfillCursoInscritoLancamentosCommandTest extends TestCase
{
    public function test_comando_backfill_esta_registrado_no_artisan(): void
    {
        Artisan::call('list', ['--raw' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('curso-inscritos:backfill-lancamentos', $output);
    }
}

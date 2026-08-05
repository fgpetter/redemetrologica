<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoDownloadDocumentoTest extends TestCase
{
    use RefreshDatabase;

    private function userComPermissaoPainel(): User
    {
        $user = User::factory()->create();

        $permission = Permission::withoutEvents(function (): Permission {
            return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
        });

        $user->permissions()->syncWithoutDetaching([$permission->id]);

        return $user;
    }

    public function test_rota_download_documento_retorna_docx_para_funcionario(): void
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso Download Documento',
            'tipo_curso' => 'OFICIAL',
            'carga_horaria' => 8,
            'objetivo' => 'Objetivo do curso',
            'publico_alvo' => 'Público alvo do curso',
            'pre_requisitos' => 'Não há',
            'exemplos_praticos' => 'Apresentação de palestras',
            'referencias_utilizadas' => 'Referências diversas',
            'conteudo_programatico' => 'Módulo 1',
        ]);

        $user = $this->userComPermissaoPainel();

        $response = $this->actingAs($user)->get(
            route('curso-download-documento', $curso->uid)
        );

        $response->assertOk();
        $response->assertDownload();
    }
}

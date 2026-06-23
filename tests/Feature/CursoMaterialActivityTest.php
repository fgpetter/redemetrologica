<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\CursoMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoMaterialActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_curso_material_possui_metodo_get_activitylog_options(): void
    {
        $material = new CursoMaterial;

        $this->assertTrue(method_exists($material, 'getActivitylogOptions'));
    }

    public function test_curso_material_pode_ser_criado(): void
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso Teste',
            'tipo_curso' => 'OFICIAL',
        ]);

        $material = CursoMaterial::query()->create([
            'uid' => uniqid('MAT-'),
            'curso_id' => $curso->id,
            'arquivo' => 'material-teste.pdf',
            'descricao' => 'Material de teste',
        ]);

        $this->assertNotNull($material->id);
        $this->assertEquals('Material de teste', $material->descricao);
    }

    public function test_curso_material_possui_relacionamento_curso(): void
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso Teste',
            'tipo_curso' => 'OFICIAL',
        ]);

        $material = CursoMaterial::query()->create([
            'uid' => uniqid('MAT-'),
            'curso_id' => $curso->id,
            'arquivo' => 'material-teste.pdf',
            'descricao' => 'Material de teste',
        ]);

        $this->assertNotNull($material->curso);
        $this->assertEquals('Curso Teste', $material->curso->descricao);
    }
}

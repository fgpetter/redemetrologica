<?php

namespace Tests\Feature;

use App\Models\AgendaCursoArquivo;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\Instrutor;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoMaterialActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_curso_material_possui_metodo_get_activitylog_options(): void
    {
        $material = new AgendaCursoArquivo;

        $this->assertTrue(method_exists($material, 'getActivitylogOptions'));
    }

    public function test_curso_material_pode_ser_criado(): void
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso Teste',
            'tipo_curso' => 'OFICIAL',
        ]);

        $instrutor = Instrutor::create(['pessoa_id' => PessoaFactory::new()->create()->id, 'situacao' => true]);
        $agenda = AgendaCursos::create(['curso_id' => $curso->id, 'instrutor_id' => $instrutor->id, 'status' => 'AGENDADO', 'tipo_agendamento' => 'ONLINE']);

        $material = AgendaCursoArquivo::query()->create([
            'uid' => uniqid('MAT-'),
            'agenda_curso_id' => $agenda->id,
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

        $instrutor = Instrutor::create(['pessoa_id' => PessoaFactory::new()->create()->id, 'situacao' => true]);
        $agenda = AgendaCursos::create(['curso_id' => $curso->id, 'instrutor_id' => $instrutor->id, 'status' => 'AGENDADO', 'tipo_agendamento' => 'ONLINE']);

        $material = AgendaCursoArquivo::query()->create([
            'uid' => uniqid('MAT-'),
            'agenda_curso_id' => $agenda->id,
            'arquivo' => 'material-teste.pdf',
            'descricao' => 'Material de teste',
        ]);

        $this->assertNotNull($material->agendaCurso);
        $this->assertEquals('Curso Teste', $material->agendaCurso->curso->descricao);
    }
}

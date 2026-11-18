<?php

use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

it('renderiza formulário HTTP e materiais do agendamento no card de treinamento', function () {
    $arquivo = (object) [
        'uid' => 'material-uid',
        'arquivo' => 'agendamento-curso-material/agenda-uid/apostila.pdf',
        'descricao' => 'Apostila da aula',
    ];
    $agenda = (object) [
        'uid' => 'agenda-uid',
        'materiaisTreinamento' => new Collection([$arquivo]),
    ];
    Storage::fake('public');
    Storage::disk('public')->put($arquivo->arquivo, 'conteúdo');

    View::share('errors', new ViewErrorBag);
    $html = Blade::render('<x-painel.agendamento-cursos.arquivos-treinamento :agendacurso="$agenda" />', ['agenda' => $agenda]);

    expect($html)
        ->toContain('Materiais do Treinamento')
        ->toContain('enctype="multipart/form-data"')
        ->toContain('name="arquivo"')
        ->toContain('name="descricao"')
        ->toContain('Apostila da aula')
        ->toContain('/storage/agendamento-curso-material/agenda-uid/apostila.pdf')
        ->toContain('method="POST"')
        ->toContain('name="_method" value="DELETE"')
        ->toContain('return confirm(')
        ->toContain('action="'.route('agendamento-curso-upload-material', ['agendaCurso' => $agenda->uid]).'"')
        ->toContain('action="'.route('agendamento-curso-delete-material', ['agendaCurso' => $agenda->uid, 'material' => $arquivo->uid]).'"');

    expect(substr_count($html, '<form'))->toBe(2);
});

it('exibe o card somente na edição persistida de curso aberto', function () {
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn () => Permission::query()->firstOrCreate(['permission' => 'funcionario']));
    $user->permissions()->syncWithoutDetaching([$permission->id]);
    $curso = Curso::query()->create(['descricao' => 'Curso de teste', 'tipo_curso' => 'OFICIAL']);
    $pessoa = PessoaFactory::new()->create();
    $instrutor = Instrutor::query()->create(['pessoa_id' => $pessoa->id, 'situacao' => true]);
    $agenda = AgendaCursos::query()->create([
        'curso_id' => $curso->id,
        'instrutor_id' => $instrutor->id,
        'status' => 'AGENDADO',
        'tipo_agendamento' => 'ONLINE',
    ]);
    $inscrito = PessoaFactory::new()->create();
    CursoInscrito::query()->create([
        'agenda_curso_id' => $agenda->id,
        'pessoa_id' => $inscrito->id,
        'nome' => 'Participante de teste',
        'email' => 'participante@example.com',
        'data_inscricao' => now(),
    ]);
    $this->actingAs($user);

    $this->get(route('agendamento-curso-insert'))->assertDontSee('Materiais do Treinamento');
    $this->get(route('agendamento-curso-in-company-insert'))
        ->assertDontSee('Materiais do Treinamento')
        ->assertDontSee('Selecionar materiais disponíveis');
    $this->get(route('agendamento-curso-insert', $agenda->uid))
        ->assertSee('Materiais do Treinamento')
        ->assertDontSee('Selecionar materiais disponíveis')
        ->assertDontSee('Enviar Docs')
        ->assertSee('Enviar Certificado')
        ->assertSee('Baixar Certificado');
});

it('não mostra interfaces legadas de materiais e preserva ações de certificados', function () {
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn () => Permission::query()->firstOrCreate(['permission' => 'funcionario']));
    $user->permissions()->syncWithoutDetaching([$permission->id]);
    $this->actingAs($user)->get(route('curso-insert'))
        ->assertDontSee('arquivos-curso')
        ->assertDontSee('Selecionar materiais disponíveis');
});

it('lista materiais da turma correta para inscrições individuais e de empresa apenas quando confirmada', function () {
    $curso = (object) ['descricao' => 'Curso compartilhado'];
    $materialConfirmado = (object) [
        'arquivo' => 'agendamento-curso-material/agenda-confirmada/individual.pdf',
        'descricao' => '<script>alert("x")</script> Material individual',
    ];
    $materialEmpresa = (object) [
        'arquivo' => 'agendamento-curso-material/agenda-empresa/empresa.pdf',
        'descricao' => 'Material empresa',
    ];
    $agendaIndividual = (object) [
        'curso' => $curso, 'status' => 'CONFIRMADO', 'data_inicio' => null, 'data_fim' => null,
        'horario' => '09h', 'endereco_local' => 'Sala 1', 'materiaisTreinamento' => new Collection([$materialConfirmado]),
    ];
    $agendaNaoConfirmada = (object) [
        'curso' => $curso, 'status' => 'AGENDADO', 'data_inicio' => null, 'data_fim' => null,
        'horario' => '10h', 'endereco_local' => 'Sala 2', 'materiaisTreinamento' => new Collection([
            (object) ['arquivo' => 'agendamento-curso-material/agenda-agendada/nao-exibir.pdf', 'descricao' => 'Não deve aparecer'],
        ]),
    ];
    $agendaEmpresa = (object) [
        'curso' => $curso, 'status' => 'CONFIRMADO', 'data_inicio' => null, 'data_fim' => null,
        'horario' => '11h', 'endereco_local' => 'Sala 3', 'materiaisTreinamento' => new Collection([$materialEmpresa]),
    ];
    $individual = fn ($id, $agenda, $empresaId = null) => (object) [
        'agenda_curso_id' => $id, 'empresa_id' => $empresaId, 'agendaCurso' => $agenda,
        'nome' => 'Pessoa inscrita', 'email' => 'pessoa@example.com',
        'empresa' => (object) ['nome_razao' => 'Empresa de teste'],
    ];
    $inscricoes = new Collection([
        $individual(1, $agendaIndividual),
        $individual(2, $agendaNaoConfirmada),
        $individual(3, $agendaEmpresa, 9),
    ]);
    Storage::fake('public');
    Storage::disk('public')->put($materialConfirmado->arquivo, 'individual');
    Storage::disk('public')->put($materialEmpresa->arquivo, 'empresa');

    $html = Blade::render('<x-painel.cliente.listagem-cursos-inscritos :cursos="$cursos" />', ['cursos' => $inscricoes]);

    expect($html)
        ->toContain('/storage/agendamento-curso-material/agenda-confirmada/individual.pdf')
        ->toContain('/storage/agendamento-curso-material/agenda-empresa/empresa.pdf')
        ->toContain('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt; Material individual')
        ->not->toContain('nao-exibir.pdf')
        ->not->toContain('Não deve aparecer');
});

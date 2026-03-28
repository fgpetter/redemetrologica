<?php

namespace Tests\Feature;

use App\Http\Requests\ConfirmaInscricaoRequest;
use App\Livewire\PainelCliente\ConfirmInscricaoCurso;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class InscricaoCursoPessoaGuardTest extends TestCase
{
    use DatabaseTransactions;

    private function uniqueCpfDigits(): string
    {
        return str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT);
    }

    private function createAgendaCursoAbertoParaInscricao(): AgendaCursos
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso teste',
            'tipo_curso' => 'OFICIAL',
        ]);

        $pessoaInstrutor = Pessoa::query()->create([
            'nome_razao' => 'Instrutor Teste',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
        ]);

        $instrutor = Instrutor::query()->create([
            'pessoa_id' => $pessoaInstrutor->id,
            'situacao' => true,
        ]);

        return AgendaCursos::query()->create([
            'uid' => (string) Str::uuid(),
            'curso_id' => $curso->id,
            'instrutor_id' => $instrutor->id,
            'status' => 'AGENDADO',
            'tipo_agendamento' => 'ONLINE',
            'inscricoes' => 1,
            'investimento' => 100,
            'investimento_associado' => 80,
        ]);
    }

    private function createClienteUser(bool $withPessoa): User
    {
        $user = User::query()->create([
            'name' => 'Cliente Teste',
            'email' => 'cliente-'.Str::random(8).'@example.com',
            'password' => bcrypt('password'),
            'temporary_password' => false,
        ]);
        $permissionId = DB::table('permissions')->where('permission', 'cliente')->value('id');
        if ($permissionId === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'permission' => 'cliente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('permission_user')->insert([
            'permission_id' => $permissionId,
            'user_id' => $user->id,
        ]);

        if ($withPessoa) {
            Pessoa::query()->create([
                'user_id' => $user->id,
                'nome_razao' => 'Cliente PF',
                'cpf_cnpj' => $this->uniqueCpfDigits(),
                'tipo_pessoa' => 'PF',
                'email' => $user->email,
            ]);
        }

        return $user->fresh();
    }

    public function test_curso_inscricao_lanca_excecao_quando_usuario_sem_pessoa(): void
    {
        $agenda = $this->createAgendaCursoAbertoParaInscricao();
        $user = $this->createClienteUser(withPessoa: false);

        $this->withoutExceptionHandling();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Usuário autenticado sem pessoa vinculada ao acessar inscrição em curso.');

        $this->actingAs($user)->get(route('curso-inscricao', ['target' => $agenda->uid]));
    }

    public function test_curso_inscricao_redireciona_ao_painel_quando_usuario_tem_pessoa(): void
    {
        $agenda = $this->createAgendaCursoAbertoParaInscricao();
        $user = $this->createClienteUser(withPessoa: true);

        $response = $this->actingAs($user)->get(route('curso-inscricao', ['target' => $agenda->uid]));

        $response->assertRedirect('painel');
    }

    public function test_confirma_inscricao_request_valida_sem_campo_id_pessoa(): void
    {
        $agenda = $this->createAgendaCursoAbertoParaInscricao();
        $user = $this->createClienteUser(withPessoa: true);

        $payload = [
            'id_curso' => $agenda->id,
            'nome' => 'Nome Participante',
            'email' => 'participante@example.com',
            'telefone' => '(11) 98765-4321',
            'cpf_cnpj' => '390.533.447-05',
            'cep' => '01310100',
            'uf' => 'SP',
            'endereco' => 'Av Paulista',
            'complemento' => null,
            'bairro' => 'Bela Vista',
            'cidade' => 'São Paulo',
        ];

        $this->actingAs($user);

        $validator = Validator::make($payload, (new ConfirmaInscricaoRequest)->rules());

        $this->assertFalse($validator->fails(), (string) $validator->errors());
        $this->assertArrayNotHasKey('id_pessoa', $validator->errors()->toArray());
    }

    public function test_confirm_inscricao_curso_livewire_lanca_excecao_sem_pessoa(): void
    {
        $agenda = $this->createAgendaCursoAbertoParaInscricao();
        $user = $this->createClienteUser(withPessoa: false);

        $this->actingAs($user);
        session()->put('curso', $agenda);

        try {
            Livewire::test(ConfirmInscricaoCurso::class);
            $this->fail('Esperada exceção ao montar inscrição sem pessoa vinculada.');
        } catch (\Throwable $e) {
            $root = $e instanceof \Illuminate\View\ViewException && $e->getPrevious() !== null
                ? $e->getPrevious()
                : $e;
            $this->assertInstanceOf(\LogicException::class, $root);
            $this->assertSame(
                'Usuário autenticado sem pessoa vinculada ao carregar inscrição em curso.',
                $root->getMessage()
            );
        }
    }
}

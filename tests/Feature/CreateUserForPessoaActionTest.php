<?php

namespace Tests\Feature;

use App\Actions\CreateUserForPessoaAction;
use App\Mail\UserPasswordReseted;
use App\Mail\UserRegistered;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreateUserForPessoaActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_usuario_quando_pessoa_nao_tem_user_id(): void
    {
        Mail::fake();

        $pessoa = PessoaFactory::new()->create([
            'user_id' => null,
            'email' => 'novo-cliente@example.com',
            'nome_razao' => 'Cliente Novo',
        ]);

        $user = CreateUserForPessoaAction::handle($pessoa->fresh());

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('novo-cliente@example.com', $user->email);
        $this->assertEquals($user->id, $pessoa->fresh()->user_id);
        $this->assertTrue($user->hasPermissionTo('cliente'));

        Mail::assertQueued(UserRegistered::class);
    }

    public function test_retorna_usuario_existente_quando_relacao_esta_integra(): void
    {
        Mail::fake();

        $pessoa = PessoaFactory::new()->withUser()->create();
        $existingUserId = $pessoa->user_id;

        $user = CreateUserForPessoaAction::handle($pessoa->fresh());

        $this->assertEquals($existingUserId, $user->id);
        $this->assertEquals(1, User::query()->count());
        Mail::assertNothingQueued();
    }

    public function test_recria_usuario_quando_user_id_esta_orfao(): void
    {
        Mail::fake();

        $pessoa = PessoaFactory::new()->create([
            'user_id' => 999999,
            'email' => 'orfao@example.com',
            'nome_razao' => 'Cliente Orfao',
        ]);

        $this->assertNull($pessoa->fresh()->user);

        $user = CreateUserForPessoaAction::handle($pessoa->fresh());

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('orfao@example.com', $user->email);
        $this->assertEquals($user->id, $pessoa->fresh()->user_id);
        $this->assertNotEquals(999999, $pessoa->fresh()->user_id);
        $this->assertTrue($user->hasPermissionTo('cliente'));

        Mail::assertQueued(UserRegistered::class);
    }

    public function test_recria_usuario_orfao_com_mailable_de_senha_resetada(): void
    {
        Mail::fake();

        $pessoa = PessoaFactory::new()->create([
            'user_id' => 999999,
            'email' => 'reset@example.com',
            'nome_razao' => 'Cliente Reset',
        ]);

        CreateUserForPessoaAction::handle($pessoa->fresh(), UserPasswordReseted::class);

        Mail::assertQueued(UserPasswordReseted::class);
        Mail::assertNotQueued(UserRegistered::class);
    }
}

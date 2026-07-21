<?php

namespace Tests\Feature;

use App\Mail\UserPasswordReseted;
use App\Mail\UserRegistered;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RecriarUsuariosOrfaosCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_nao_cria_usuario_nem_enfileira_email(): void
    {
        Mail::fake();

        PessoaFactory::new()->create([
            'user_id' => 999999,
            'email' => 'orfao@example.com',
            'nome_razao' => 'Cliente Orfao',
        ]);

        $this->artisan('app:recriar-usuarios-orfaos', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertEquals(0, User::query()->count());
        Mail::assertNothingQueued();
    }

    public function test_execucao_real_cria_usuario_e_enfileira_user_password_reseted(): void
    {
        Mail::fake();

        $pessoa = PessoaFactory::new()->create([
            'user_id' => 999999,
            'email' => 'orfao@example.com',
            'nome_razao' => 'Cliente Orfao',
        ]);

        $this->artisan('app:recriar-usuarios-orfaos')
            ->assertSuccessful();

        $this->assertEquals(1, User::query()->count());
        $this->assertEquals(User::query()->value('id'), $pessoa->fresh()->user_id);
        Mail::assertQueued(UserPasswordReseted::class);
        Mail::assertNotQueued(UserRegistered::class);
    }

    public function test_pessoa_sem_email_e_ignorada_com_sucesso(): void
    {
        Mail::fake();

        $logPath = storage_path('logs/RecriarUsuariosOrfaosLog.log');

        if (file_exists($logPath)) {
            unlink($logPath);
        }

        PessoaFactory::new()->create([
            'user_id' => 999999,
            'email' => null,
            'nome_razao' => 'Sem Email',
        ]);

        $this->artisan('app:recriar-usuarios-orfaos')
            ->assertSuccessful();

        $this->assertEquals(0, User::query()->count());
        Mail::assertNothingQueued();
        $this->assertFileExists($logPath);
        $this->assertStringContainsString('SKIP', (string) file_get_contents($logPath));
    }

    public function test_step_limita_quantidade_de_pessoas_processadas(): void
    {
        Mail::fake();

        PessoaFactory::new()->create([
            'user_id' => 999998,
            'email' => 'primeiro@example.com',
            'nome_razao' => 'Primeiro Orfao',
        ]);

        PessoaFactory::new()->create([
            'user_id' => 999999,
            'email' => 'segundo@example.com',
            'nome_razao' => 'Segundo Orfao',
        ]);

        $this->artisan('app:recriar-usuarios-orfaos', ['--step' => 1])
            ->assertSuccessful();

        $this->assertEquals(1, User::query()->count());
        Mail::assertQueued(UserPasswordReseted::class, 1);
    }
}

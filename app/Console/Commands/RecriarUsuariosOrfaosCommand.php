<?php

namespace App\Console\Commands;

use App\Actions\CreateUserForPessoaAction;
use App\Mail\UserPasswordReseted;
use App\Models\Pessoa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Throwable;

class RecriarUsuariosOrfaosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recriar-usuarios-orfaos
                            {--dry-run : Lista órfãos sem criar usuário nem enviar e-mail}
                            {--step= : Limita a quantidade de pessoas processadas (ex.: 1 para teste)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recria usuários de pessoas com user_id órfão e envia e-mail de senha resetada';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $step = $this->option('step');

        if ($step !== null) {
            $step = (int) $step;

            if ($step < 1) {
                $this->error('O parâmetro --step deve ser um inteiro maior ou igual a 1.');

                return self::FAILURE;
            }
        }

        $query = Pessoa::query()
            ->whereNotNull('user_id')
            ->whereDoesntHave('user')
            ->orderBy('id');

        if ($step !== null) {
            $query->limit($step);
        }

        $pessoas = $query->get();

        if ($pessoas->isEmpty()) {
            $this->info('Nenhuma pessoa com user_id órfão encontrada.');

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;
        $delayIndex = 0;

        foreach ($pessoas as $pessoa) {
            if (! $this->hasValidEmail($pessoa->email)) {
                $this->warn("Pessoa {$pessoa->id} ({$pessoa->nome_razao}) ignorada: e-mail inválido ou ausente.");
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $this->line("Dry-run: pessoa {$pessoa->id} ({$pessoa->nome_razao}) — user_id órfão {$pessoa->user_id}");

                continue;
            }

            try {
                $delayIndex++;
                CreateUserForPessoaAction::handle(
                    $pessoa,
                    UserPasswordReseted::class,
                    $delayIndex * 60,
                );
                $this->info("Pessoa {$pessoa->id} ({$pessoa->nome_razao}): usuário recriado (e-mail em {$delayIndex} min).");
                $created++;
            } catch (Throwable $exception) {
                $this->error("Pessoa {$pessoa->id} ({$pessoa->nome_razao}): falha — {$exception->getMessage()}");
                $failed++;
            }
        }

        if ($dryRun) {
            $this->info("Dry-run: {$pessoas->count()} pessoa(s) com user_id órfão encontrada(s).");

            return self::SUCCESS;
        }

        $this->info("Concluído. Criados {$created}. Ignorados {$skipped}. Falhas {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function hasValidEmail(?string $email): bool
    {
        if ($email === null || trim($email) === '') {
            return false;
        }

        return Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email']]
        )->passes();
    }
}

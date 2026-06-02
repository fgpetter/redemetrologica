<?php

namespace App\Console\Commands;

use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportInterlabAnalistasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-interlab-analistas
                            {--dry-run : Simula a importacao sem persistir no banco}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa analistas hard coded para interlab_analistas usando a model';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            try {
                DB::transaction(function (): void {
                    $this->importarDadosHardCoded();
                    throw new \RuntimeException('__dry_run_rollback__');
                });
            } catch (\RuntimeException $exception) {
                if ($exception->getMessage() !== '__dry_run_rollback__') {
                    throw $exception;
                }
            }

            $this->warn('Dry-run concluido: nenhuma linha foi persistida.');

            return self::SUCCESS;
        }

        $this->importarDadosHardCoded();

        return self::SUCCESS;
    }

    private function importarDadosHardCoded(): void
    {
        $dadosAnalistas = $this->dadosAnalistas();

        $importados = 0;
        $ignorados = 0;

        foreach ($dadosAnalistas as $index => $dados) {
            $linha = $index + 1;
            $interlabInscritoId = (int) $dados['interlab_inscrito_id'];
            $nome = trim($dados['nome']);
            $email = trim($dados['email']);
            $telefone = trim($dados['telefone']);
            $tagSenha = trim($dados['tag_senha']);

            if (
                $interlabInscritoId <= 0 ||
                $nome === '' ||
                $email === '' ||
                $tagSenha === ''
            ) {
                $this->warn("Registro {$linha} ignorado: campos obrigatorios ausentes.");
                $ignorados++;

                continue;
            }

            if (! InterlabInscrito::query()->whereKey($interlabInscritoId)->exists()) {
                $this->warn("Linha {$linha} ignorada: interlab_inscrito_id {$interlabInscritoId} inexistente.");
                $ignorados++;

                continue;
            }

            InterlabAnalista::query()->create([
                'interlab_inscrito_id' => $interlabInscritoId,
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone !== '' ? $telefone : '0',
                'tag_senha' => $tagSenha,
            ]);

            $importados++;
        }

        $this->info("Importacao finalizada. Importados: {$importados}. Ignorados: {$ignorados}.");
    }

    /**
     * @return array<int, array{interlab_inscrito_id: int, nome: string, email: string, telefone: string, tag_senha: string}>
     */
    private function dadosAnalistas(): array
    {
        return [
            ['interlab_inscrito_id' => 982, 'nome' => 'Keila Catarina Prior', 'email' => 'qualidade@cedisa.org.br', 'telefone' => '4934428800', 'tag_senha' => 'SA_999'],
            ['interlab_inscrito_id' => 982, 'nome' => 'Lauren Ventura Parisotto', 'email' => 'qualidade@cedisa.org.br', 'telefone' => '', 'tag_senha' => 'SA_998'],
            ['interlab_inscrito_id' => 1021, 'nome' => 'Fabrine Finkler', 'email' => 'qualidade@sanuvitas.com.br', 'telefone' => '5434622195', 'tag_senha' => 'SA_997'],
            ['interlab_inscrito_id' => 1021, 'nome' => 'Fabrine Finkler', 'email' => 'fabrine.finkler@sanuvitas.com.br', 'telefone' => '', 'tag_senha' => 'SA_996'],
            ['interlab_inscrito_id' => 1022, 'nome' => 'Eduarda Freitas Costa', 'email' => 'lanapa@lanapa.com.br', 'telefone' => '81987750689', 'tag_senha' => 'SA_995'],
            ['interlab_inscrito_id' => 1022, 'nome' => 'Eduarda Freitas Costa', 'email' => 'eduarda.costa@lanapa.com.br', 'telefone' => '', 'tag_senha' => 'SA_994'],
            ['interlab_inscrito_id' => 1022, 'nome' => 'Saruanna Millena dos Santos Clemente', 'email' => 'lanapa@lanapa.com.br', 'telefone' => '', 'tag_senha' => 'SA_993'],
            ['interlab_inscrito_id' => 1029, 'nome' => 'Elisson P. Ribeiro da Silva', 'email' => 'qualidade@jflab.com.br', 'telefone' => '', 'tag_senha' => 'SA_992'],
            ['interlab_inscrito_id' => 1029, 'nome' => 'Elisson P. Ribeiro da Silva', 'email' => 'tecnico@jflab.com.br', 'telefone' => '', 'tag_senha' => 'SA_991'],
            ['interlab_inscrito_id' => 1029, 'nome' => 'Fabiana Arellano', 'email' => 'tecnico@jflab.com.br', 'telefone' => '', 'tag_senha' => 'SA_990'],
            ['interlab_inscrito_id' => 1029, 'nome' => 'Jose de Fabio', 'email' => 'difabio@jflab.com.br', 'telefone' => '', 'tag_senha' => 'SA_989'],
            ['interlab_inscrito_id' => 1034, 'nome' => 'Gabriela Monteiro de Andrade', 'email' => 'qualidadech@mercolab.com.br', 'telefone' => '4998100183', 'tag_senha' => 'SA_988'],
            ['interlab_inscrito_id' => 1034, 'nome' => 'Gabriela Monteiro de Andrade', 'email' => 'tecnicosch@mercolab.com.br', 'telefone' => '', 'tag_senha' => 'SA_987'],
            ['interlab_inscrito_id' => 1036, 'nome' => 'CLAUDIO I MIYAJI', 'email' => 'spave@uol.com.br', 'telefone' => '1138312984', 'tag_senha' => 'SA_977'],
            ['interlab_inscrito_id' => 1036, 'nome' => 'Claudio Issamu Miyaji', 'email' => 'cmiyaji@yahoo.com.br', 'telefone' => '', 'tag_senha' => 'SA_985'],
            ['interlab_inscrito_id' => 1063, 'nome' => 'Rafaela Bom Morgan', 'email' => 'rafaela.morgan@mbrf.com', 'telefone' => '4932118015', 'tag_senha' => 'SA_9894'],
            ['interlab_inscrito_id' => 1063, 'nome' => 'Claudia Cordeiro Correa Zambonin', 'email' => 'claudia.cordeiro@mbrf.com', 'telefone' => '', 'tag_senha' => 'SA_983'],
            ['interlab_inscrito_id' => 1078, 'nome' => 'Ana Maria Paiva Oliveira', 'email' => 'ana@labportobelo.com.br', 'telefone' => '5199789598', 'tag_senha' => 'SA_982'],
            ['interlab_inscrito_id' => 1078, 'nome' => 'Ana Maria Paiva Oliveira', 'email' => 'ana@labportobelo.com.br', 'telefone' => '', 'tag_senha' => 'SA_981'],
            ['interlab_inscrito_id' => 1082, 'nome' => 'Joana Mathias', 'email' => 'joana.mathias@agrogen.com.br', 'telefone' => '51995956320', 'tag_senha' => 'SA_980'],
            ['interlab_inscrito_id' => 1082, 'nome' => 'Raquel Silveira Silva', 'email' => 'laboratorio@agrogen.com.br', 'telefone' => '', 'tag_senha' => 'SA_979'],
            ['interlab_inscrito_id' => 1082, 'nome' => 'Lusiane Rodrigues dos Santos', 'email' => 'laboratorio@agrogen.com.br', 'telefone' => '', 'tag_senha' => 'SA_978'],
        ];
    }
}

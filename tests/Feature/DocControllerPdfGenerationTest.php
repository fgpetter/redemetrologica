<?php

namespace Tests\Feature;

use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\DadosGeraDoc;
use App\Models\Endereco;
use App\Models\Instrutor;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\Pessoa;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\FakePdfBuilder;
use Tests\TestCase;

class DocControllerPdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-25 13:49:14'));

        $fake = new class extends FakePdfBuilder
        {
            public function save(string $path): self
            {
                parent::save($path);

                if (! is_dir(dirname($path))) {
                    mkdir(dirname($path), 0777, true);
                }

                file_put_contents($path, '%PDF-1.4');

                return $this;
            }
        };

        Pdf::swap($fake);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_download_gera_pdf_tag_senha_com_view_correta(): void
    {
        $dadosDoc = DadosGeraDoc::query()->create([
            'tipo' => 'tag_senha',
            'content' => [
                'tag_senha' => 'TAG123',
                'interlab_nome' => 'PEP Teste',
                'empresa_nome_razao' => 'Empresa Teste',
                'laboratorio_nome' => 'Lab Teste',
                'empresa_cpf_cnpj' => '12.345.678/0001-90',
                'informacoes_inscricao' => 'Opção A',
            ],
        ]);

        $path = Storage::path($dadosDoc->storage_path);

        $response = $this->get(route('dados-doc.download', ['link' => $dadosDoc->link]));

        $response->assertOk();
        Pdf::assertViewIs('certificados.tag-senha');
        Pdf::assertSaved($path);
    }

    public function test_download_gera_pdf_tag_senha_analista_com_view_correta(): void
    {
        $dadosDoc = DadosGeraDoc::query()->create([
            'tipo' => 'tag_senha_analista',
            'content' => [
                'tag_senha' => 'TAG-ANA-123',
                'interlab_nome' => 'PEP Teste',
                'empresa_nome_razao' => 'Empresa Teste',
                'laboratorio_nome' => 'Lab Teste',
                'analista_nome' => 'Analista Teste',
                'empresa_cpf_cnpj' => '12.345.678/0001-90',
                'informacoes_inscricao' => 'Opção A',
            ],
        ]);

        $path = Storage::path($dadosDoc->storage_path);

        $response = $this->get(route('dados-doc.download', ['link' => $dadosDoc->link]));

        $response->assertOk();
        Pdf::assertViewIs('certificados.tag-senha-analista');
        Pdf::assertSaved($path);
    }

    public function test_download_persiste_caminho_com_timestamp_para_nomes_longos(): void
    {
        $analistaNome = 'Elisabete Aparecida Lopes Guastalli';
        $laboratorioNome = 'Instituto Biologico Servico Laboratorial Regional de Pesquisa em Sanidade Avicola';

        $dadosDoc = DadosGeraDoc::query()->create([
            'tipo' => 'tag_senha_analista',
            'content' => [
                'tag_senha' => 'TAG-ANA-123',
                'interlab_nome' => 'PEP Teste',
                'empresa_nome_razao' => 'Empresa Teste',
                'laboratorio_nome' => $laboratorioNome,
                'analista_nome' => $analistaNome,
                'empresa_cpf_cnpj' => '12.345.678/0001-90',
                'informacoes_inscricao' => 'Opção A',
            ],
        ]);

        $linkUuid = $dadosDoc->link;
        $timestamp = (string) now()->timestamp;
        $expectedRelativePath = $dadosDoc->storage_path;

        $response = $this->get(route('dados-doc.download', ['link' => $linkUuid]));

        $response->assertOk();

        $dadosDoc->refresh();

        $persistedPath = $dadosDoc->getRawOriginal('file_name');

        $this->assertSame($expectedRelativePath, $persistedPath);
        $this->assertLessThanOrEqual(255, strlen($persistedPath));
        $this->assertStringEndsWith("_{$timestamp}.pdf", $persistedPath);
        $this->assertStringNotContainsString($linkUuid, $persistedPath);
        $this->assertSame($linkUuid, $dadosDoc->link);
    }

    public function test_download_gera_pdf_certificado_com_view_correta(): void
    {
        $inscrito = $this->criarCursoInscritoParaCertificado();
        $dadosDoc = DadosGeraDoc::query()->create([
            'tipo' => 'certificado',
            'content' => [
                'participante_id' => $inscrito->id,
                'participante_nome' => 'Participante Certificado',
                'curso_nome' => 'Curso de Metrologia',
                'curso_data' => '01/01/2025 a 05/01/2025',
                'conteudo_programatico' => 'Módulo 1',
                'carga_horaria' => '40',
                'instrutor_nome' => 'Instrutor Teste',
            ],
        ]);

        $path = Storage::path($dadosDoc->storage_path);

        $response = $this->get(route('dados-doc.download', ['link' => $dadosDoc->link]));

        $response->assertOk();
        Pdf::assertViewIs('certificados.certificado');
        Pdf::assertSaved(fn ($pdf, $savedPath) => $pdf->viewData['localRealizacaoCertificado'] === 'Associação Rede de Metrologia e Ensaios do RS'
            && $savedPath === $path);
        Pdf::assertSaved($path);
    }

    public function test_download_gera_pdf_certificado_interlab_com_view_correta(): void
    {
        $inscrito = $this->criarInterlabInscritoParaCertificado();
        $dadosDoc = DadosGeraDoc::query()->create([
            'tipo' => 'certificado_interlab',
            'content' => [
                'participante_id' => $inscrito->id,
                'laboratorio_nome' => $inscrito->laboratorio->nome,
            ],
        ]);

        $path = Storage::path($dadosDoc->storage_path);

        $response = $this->get(route('dados-doc.download', ['link' => $dadosDoc->link]));

        $response->assertOk();
        Pdf::assertViewIs('certificados.certificado-interlab');
        Pdf::assertViewHas('participante');
        Pdf::assertSaved(fn ($pdf) => $pdf->viewData['participante']->id === $inscrito->id);
        Pdf::assertSaved($path);
    }

    public function test_certificado_interlab_coloca_nome_do_laboratorio_em_maiusculas_com_acentos(): void
    {
        $inscrito = $this->criarInterlabInscritoParaCertificado();
        $inscrito->laboratorio->update([
            'nome' => 'Santé Laboratório de Análises Clínicas Ltda',
        ]);
        $inscrito->load(['laboratorio', 'agendaInterlab.interlab']);

        $html = view('certificados.certificado-interlab', [
            'participante' => $inscrito,
        ])->render();

        $this->assertStringContainsString('SANTÉ LABORATÓRIO DE ANÁLISES CLÍNICAS LTDA', $html);
    }

    private function criarCursoInscritoParaCertificado(): CursoInscrito
    {
        $pessoaInstrutor = Pessoa::query()->create([
            'nome_razao' => 'Instrutor PDF Teste',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        $instrutor = Instrutor::query()->create([
            'pessoa_id' => $pessoaInstrutor->id,
            'situacao' => true,
        ]);

        $agenda = AgendaCursos::query()->create([
            'uid' => (string) Str::uuid(),
            'curso_id' => Curso::query()->create(['descricao' => 'Curso PDF Teste', 'tipo_curso' => 'OFICIAL'])->id,
            'instrutor_id' => $instrutor->id,
            'empresa_id' => null,
            'status' => 'REALIZADO',
            'tipo_agendamento' => 'ONLINE',
            'inscricoes' => 1,
            'investimento' => 100,
            'investimento_associado' => 80,
        ]);

        $pessoa = Pessoa::query()->create([
            'nome_razao' => 'Participante PDF Teste',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        return CursoInscrito::query()->create([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'data_inscricao' => now(),
            'email' => 'participante@example.com',
            'nome' => 'Participante PDF Teste',
        ]);
    }

    private function criarInterlabInscritoParaCertificado(): InterlabInscrito
    {
        $agenda = AgendaInterlabFactory::new()->create();
        $empresa = PessoaFactory::new()->create();
        $endereco = Endereco::query()->create(['pessoa_id' => $empresa->id]);
        $laboratorio = InterlabLaboratorio::query()->create([
            'empresa_id' => $empresa->id,
            'endereco_id' => $endereco->id,
            'nome' => 'Laboratório PDF Teste',
        ]);

        return InterlabInscritoFactory::new()->create([
            'agenda_interlab_id' => $agenda->id,
            'empresa_id' => $empresa->id,
            'laboratorio_id' => $laboratorio->id,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Actions\GenerateDocxFromTemplateAction;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Tests\TestCase;

class GenerateCursoDocumentoTest extends TestCase
{
    public function test_gera_docx_do_curso_substituindo_as_tags_do_template(): void
    {
        $data = [
            'descricao' => 'Curso de Teste Unitário',
            'carga_horaria' => 8,
            'objetivo' => 'Objetivo de teste',
            'publico_alvo' => 'Público de teste',
            'pre_requisitos' => 'Não há',
            'exemplos_praticos' => 'Exemplos de teste',
            'referencias_utilizadas' => 'Referências de teste',
            'conteudo_programatico' => 'Conteúdo de teste',
        ];

        $templatePath = storage_path('app/templates/Curso.docx');
        $outputRelativePath = 'docs/teste_gerar_docx_curso.docx';

        $relativePath = (new GenerateDocxFromTemplateAction)
            ->execute($templatePath, $data, [], $outputRelativePath);

        $fullPath = Storage::path("public/{$relativePath}");

        $this->assertFileExists($fullPath);

        $texto = $this->extrairTexto($fullPath);

        foreach ($data as $valor) {
            $this->assertStringContainsString((string) $valor, $texto);
        }

        Storage::delete("public/{$relativePath}");
    }

    private function extrairTexto(string $path): string
    {
        $phpWord = IOFactory::load($path);
        $texto = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $texto .= $element->getText()."\n";
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $child) {
                        if (method_exists($child, 'getText')) {
                            $texto .= $child->getText()."\n";
                        }
                    }
                }
            }
        }

        return $texto;
    }
}

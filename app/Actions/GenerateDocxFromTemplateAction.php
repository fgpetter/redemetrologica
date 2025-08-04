<?php

namespace App\Actions;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class GenerateDocxFromTemplateAction
{
    
    public function execute(string $templatePath, array $data, array  $blocks, string $outputRelativePath): string
    {
        if (!file_exists($templatePath)) {
            throw new \Exception("O template do documento não foi encontrado em {$templatePath}");
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValues($data);

        foreach ($blocks as $blockName => $replacements) {
            $templateProcessor->cloneBlock(
                $blockName,
                0,      // ignora o número fixo; usa size($replacements)
                true,   // remove as tags ${BLOCK}/${/BLOCK}
                false,  // sem quebras extras
                $replacements
            );
        }

        // $absolutePathToSave = Storage::disk('public')->path($outputRelativePath);
        $absolutePathToSave = Storage::path("public/{$outputRelativePath}");
        

        $templateProcessor->saveAs($absolutePathToSave);

        return $outputRelativePath;
    }
}

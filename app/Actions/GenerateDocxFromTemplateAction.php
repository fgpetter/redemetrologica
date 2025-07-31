<?php

namespace App\Actions;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class GenerateDocxFromTemplateAction
{
    
    public function execute(string $templatePath, array $data, string $outputRelativePath): string
    {
        if (!file_exists($templatePath)) {
            throw new \Exception("O template do documento não foi encontrado em {$templatePath}");
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValues($data);

        $absolutePathToSave = Storage::disk('public')->path($outputRelativePath);

        $templateProcessor->saveAs($absolutePathToSave);

        return $outputRelativePath;
    }
}

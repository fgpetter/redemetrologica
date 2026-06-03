<?php

namespace Tests\Feature;

use App\Actions\FileUploadAction;
use Illuminate\Http\Request;
use Tests\TestCase;

class FileUploadActionTest extends TestCase
{
    public function test_retorna_null_quando_request_nao_tem_arquivo(): void
    {
        $request = new Request;

        $result = FileUploadAction::handle($request, 'arquivo', 'uploads');

        $this->assertNull($result);
    }

    public function test_retorna_null_quando_campo_diferente_do_esperado(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $request = new Request;
        $request->files->set('outro_campo', $file);

        $result = FileUploadAction::handle($request, 'arquivo', 'uploads');

        $this->assertNull($result);
    }
}

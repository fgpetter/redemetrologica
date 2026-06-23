<?php

namespace Tests\Feature;

use App\Actions\FileUploadAction;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InterventionImageUploadTest extends TestCase
{
    use DatabaseTransactions;

    private string $uploadPath = 'test-intervention-uploads';

    protected function tearDown(): void
    {
        $directory = public_path($this->uploadPath);

        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }

        $tempDirectories = File::directories(public_path());

        foreach ($tempDirectories as $directory) {
            if (str_starts_with(basename($directory), 'Temp')) {
                File::deleteDirectory($directory);
            }
        }

        parent::tearDown();
    }

    private function usuarioFuncionario(): User
    {
        $user = User::factory()->create();

        $permission = Permission::withoutEvents(function (): Permission {
            return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
        });

        $user->permissions()->syncWithoutDetaching([$permission->id]);

        return $user;
    }

    private function createTestPng(int $width, int $height): string
    {
        $path = sys_get_temp_dir().'/test-image-'.uniqid('', true).'.png';
        $image = imagecreatetruecolor($width, $height);
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }

    public function test_file_upload_action_redimensiona_e_salva_como_jpg(): void
    {
        $destination = public_path($this->uploadPath);

        if (! File::isDirectory($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $pngPath = $this->createTestPng(2000, 1200);
        $file = new UploadedFile($pngPath, 'foto-teste.png', 'image/png', null, true);

        $request = Request::create('/', 'POST', [], [], ['foto' => $file]);

        $fileName = FileUploadAction::handle(
            $request,
            'foto',
            $this->uploadPath,
            ['width' => 750, 'height' => 750],
        );

        $this->assertNotNull($fileName);
        $this->assertStringEndsWith('.jpg', $fileName);

        $savedPath = public_path($this->uploadPath.'/'.$fileName);
        $this->assertFileExists($savedPath);

        [$width, $height] = getimagesize($savedPath);
        $this->assertLessThanOrEqual(750, $width);
        $this->assertLessThanOrEqual(750, $height);

        @unlink($pngPath);
    }

    public function test_post_controller_store_image_redimensiona_imagem_enviada(): void
    {
        $pngPath = $this->createTestPng(2000, 2000);
        $file = new UploadedFile($pngPath, 'upload.png', 'image/png', null, true);

        $response = $this->actingAs($this->usuarioFuncionario())
            ->post(route('image-upload'), ['upload' => $file]);

        $response->assertOk();
        $response->assertJson([
            'uploaded' => 1,
        ]);

        $fileName = $response->json('fileName');
        $this->assertNotEmpty($fileName);

        $tempFolders = collect(File::directories(public_path()))
            ->filter(fn (string $path): bool => str_starts_with(basename($path), 'Temp'))
            ->values();

        $this->assertNotEmpty($tempFolders);

        $savedPath = $tempFolders->first().'/'.$fileName;
        $this->assertFileExists($savedPath);

        [$width, $height] = getimagesize($savedPath);
        $this->assertLessThanOrEqual(750, $width);
        $this->assertLessThanOrEqual(750, $height);

        @unlink($pngPath);
    }
}

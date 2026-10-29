<?php

use App\Models\AgendaInterlab;
use App\Models\Interlab;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

it('registra os detalhes da falha de validação do material', function () {
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn () => Permission::query()->firstOrCreate(['permission' => 'funcionario']));
    $user->permissions()->syncWithoutDetaching([$permission->id]);
    $interlab = Interlab::query()->create(['nome' => 'Interlab teste', 'tipo' => 'INTERLABORATORIAL']);
    $agenda = AgendaInterlab::query()->create([
        'interlab_id' => $interlab->id,
        'status' => 'CONFIRMADO',
        'inscricao' => true,
        'ano_referencia' => 2026,
    ]);

    $logger = Mockery::mock(\Psr\Log\LoggerInterface::class);
    $logger->shouldReceive('info')->once()->withArgs(function ($message, $context) use ($user, $agenda) {
        return $message === 'Erro de validação de material do Interlab'
            && $context['user_id'] === $user->id
            && $context['agenda_interlab_uid'] === $agenda->uid
            && isset($context['failed_rules']['arquivo']['Mimes'])
            && $context['arquivo']['nome'] === 'material.txt'
            && $context['arquivo']['mime_informado'] === 'text/plain'
            && $context['arquivo']['upload_error'] === UPLOAD_ERR_OK
            && ! array_key_exists('request', $context);
    });
    Log::shouldReceive('channel')->once()->with('validation')->andReturn($logger);

    $this->actingAs($user)->from('/anterior')->post(route('agenda-interlab-upload-material', $agenda->uid), [
        'descricao' => 'Descrição privada',
        'arquivo' => UploadedFile::fake()->create('material.txt', 10, 'text/plain'),
    ])->assertRedirect('/anterior')->assertSessionHasErrors('arquivo');

    expect($agenda->materiais()->count())->toBe(0);
});

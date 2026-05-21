<?php

namespace Tests\Feature;

use App\Http\Requests\StoreAgendaInterlabRequest;
use App\Models\Interlab;
use Database\Factories\InterlabFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreAgendaInterlabRequestTest extends TestCase
{
    public function test_analistas_obrigatorio_quando_interlab_avaliacao_e_analista(): void
    {
        $interlab = InterlabFactory::new()->create(['avaliacao' => 'ANALISTA']);

        $validator = $this->makeValidator(
            $this->payloadBase($interlab, [['descricao' => 'Bloco 1', 'valor' => '100', 'valor_assoc' => '80']])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('valores.0.analistas', $validator->errors()->toArray());
    }

    public function test_analistas_valido_quando_interlab_avaliacao_e_analista(): void
    {
        $interlab = InterlabFactory::new()->create(['avaliacao' => 'ANALISTA']);

        $validator = $this->makeValidator(
            $this->payloadBase($interlab, [['descricao' => 'Bloco 1', 'valor' => '100', 'valor_assoc' => '80', 'analistas' => 2]])
        );

        $this->assertFalse($validator->fails(), (string) $validator->errors());
    }

    public function test_analistas_opcional_quando_interlab_avaliacao_e_laboratorial(): void
    {
        $interlab = InterlabFactory::new()->create(['avaliacao' => 'LABORATORIAL']);

        $validator = $this->makeValidator(
            $this->payloadBase($interlab, [['descricao' => 'Bloco 1', 'valor' => '100', 'valor_assoc' => '80']])
        );

        $this->assertFalse($validator->fails(), (string) $validator->errors());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function makeValidator(array $payload): \Illuminate\Validation\Validator
    {
        $formRequest = StoreAgendaInterlabRequest::createFrom(
            Request::create('/agenda-interlab/create', 'POST', $payload)
        );
        $formRequest->setContainer($this->app);

        return Validator::make(
            $formRequest->all(),
            $formRequest->rules(),
            $formRequest->messages()
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $valores
     * @return array<string, mixed>
     */
    private function payloadBase(Interlab $interlab, array $valores): array
    {
        return [
            'interlab_id' => $interlab->id,
            'status' => 'AGENDADO',
            'data_inicio' => now()->toDateString(),
            'data_limite_inscricao' => now()->addDays(10)->toDateString(),
            'valores' => $valores,
        ];
    }
}

<?php

use App\Models\Curso;
use App\Models\CursoMaterial;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

it('moves historical changes out of properties while preserving custom data and batch UUIDs', function () {
    $activityModelClass = get_class(new class extends SpatieActivity
    {
        protected $connection = 'activitylog_migration_test';
    });

    config([
        'database.connections.activitylog_migration_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
        'activitylog.activity_model' => $activityModelClass,
    ]);

    DB::purge('activitylog_migration_test');
    Schema::connection('activitylog_migration_test')->create('activity_log', function (Blueprint $table): void {
        $table->id();
        $table->string('log_name')->nullable();
        $table->text('description');
        $table->string('subject_type')->nullable();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->string('causer_type')->nullable();
        $table->unsignedBigInteger('causer_id')->nullable();
        $table->json('properties')->nullable();
        $table->uuid('batch_uuid')->nullable();
        $table->timestamps();
    });

    $batchUuid = 'aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee';
    DB::connection('activitylog_migration_test')->table('activity_log')->insert([
        [
            'description' => 'updated',
            'properties' => json_encode([
                'attributes' => ['name' => 'New name'],
                'old' => ['name' => 'Old name'],
                'context' => ['source' => 'legacy'],
            ], JSON_THROW_ON_ERROR),
            'batch_uuid' => $batchUuid,
        ],
        [
            'description' => 'custom activity',
            'properties' => '{"attributes":{"name":"new"},"7":"manual"}',
            'batch_uuid' => '11111111-2222-4333-8444-555555555555',
        ],
        ['description' => 'null properties', 'properties' => 'null', 'batch_uuid' => null],
        ['description' => 'string properties', 'properties' => json_encode('custom string', JSON_THROW_ON_ERROR), 'batch_uuid' => null],
        ['description' => 'numeric properties', 'properties' => '42', 'batch_uuid' => null],
        ['description' => 'boolean properties', 'properties' => 'true', 'batch_uuid' => null],
        ['description' => 'list properties', 'properties' => '[]', 'batch_uuid' => null],
    ]);

    $migration = require database_path('migrations/2026_10_29_093647_upgrade_activity_log_attribute_changes.php');
    $migration->up();

    $converted = DB::connection('activitylog_migration_test')->table('activity_log')->where('id', 1)->first();
    expect(json_decode($converted->attribute_changes, true, flags: JSON_THROW_ON_ERROR))->toBe([
        'attributes' => ['name' => 'New name'],
        'old' => ['name' => 'Old name'],
    ]);
    expect(json_decode($converted->properties, true, flags: JSON_THROW_ON_ERROR))->toBe([
        'context' => ['source' => 'legacy'],
    ]);
    expect($converted->batch_uuid)->toBe($batchUuid);

    $customActivity = DB::connection('activitylog_migration_test')->table('activity_log')->where('id', 2)->first();
    expect(json_decode($customActivity->attribute_changes, true, flags: JSON_THROW_ON_ERROR))
        ->toBe(['attributes' => ['name' => 'new']]);
    expect($customActivity->properties)->toBe('{"7":"manual"}');

    foreach ([3 => 'null', 4 => '"custom string"', 5 => '42', 6 => 'true', 7 => '[]'] as $id => $properties) {
        $scalarPropertiesActivity = DB::connection('activitylog_migration_test')->table('activity_log')->where('id', $id)->first();
        expect($scalarPropertiesActivity->attribute_changes)->toBeNull()
            ->and($scalarPropertiesActivity->properties)->toBe($properties);
    }

    $migration->down();

    $restored = DB::connection('activitylog_migration_test')->table('activity_log')->where('id', 1)->first();
    expect(Schema::connection('activitylog_migration_test')->hasColumn('activity_log', 'attribute_changes'))->toBeFalse();
    expect(json_decode($restored->properties, true, flags: JSON_THROW_ON_ERROR))->toBe([
        'attributes' => ['name' => 'New name'],
        'old' => ['name' => 'Old name'],
        'context' => ['source' => 'legacy'],
    ]);
    expect($restored->batch_uuid)->toBe($batchUuid);
    $restoredCustom = DB::connection('activitylog_migration_test')->table('activity_log')->where('id', 2)->first();
    expect(json_decode($restoredCustom->properties, true, flags: JSON_THROW_ON_ERROR))->toBe([
        'attributes' => ['name' => 'new'],
        7 => 'manual',
    ]);

    Schema::connection('activitylog_migration_test')->table('activity_log', function (Blueprint $table): void {
        $table->json('attribute_changes')->nullable()->after('causer_id');
    });
    DB::connection('activitylog_migration_test')->table('activity_log')->insert([
        [
            'description' => 'safe row before collision',
            'properties' => json_encode(['source' => 'legacy'], JSON_THROW_ON_ERROR),
            'attribute_changes' => json_encode(['attributes' => ['name' => 'New']], JSON_THROW_ON_ERROR),
        ],
        [
            'description' => 'overlapping properties',
            'properties' => json_encode(['attributes' => ['custom' => true]], JSON_THROW_ON_ERROR),
            'attribute_changes' => json_encode(['attributes' => ['name' => 'New']], JSON_THROW_ON_ERROR),
        ],
        [
            'description' => 'non-object properties',
            'properties' => 'null',
            'attribute_changes' => json_encode(['old' => ['name' => 'Old']], JSON_THROW_ON_ERROR),
        ],
    ]);

    expect(fn () => $migration->down())
        ->toThrow(RuntimeException::class, 'activity #9 has overlapping properties and attribute_changes keys');
    $safeRow = DB::connection('activitylog_migration_test')->table('activity_log')->where('id', 8)->first();
    expect($safeRow->properties)->toBe('{"source":"legacy"}')
        ->and(json_decode($safeRow->attribute_changes, true, flags: JSON_THROW_ON_ERROR))->toBe(['attributes' => ['name' => 'New']]);

    DB::connection('activitylog_migration_test')->table('activity_log')->where('id', 9)->update([
        'properties' => json_encode(['context' => 'manual'], JSON_THROW_ON_ERROR),
    ]);
    expect(fn () => $migration->down())
        ->toThrow(RuntimeException::class, 'activity #10 has non-object JSON in properties');
    expect(Schema::connection('activitylog_migration_test')->hasColumn('activity_log', 'attribute_changes'))->toBeTrue()
        ->and(DB::connection('activitylog_migration_test')->table('activity_log')->where('id', 8)->value('properties'))->toBe('{"source":"legacy"}');
});

it('falls back to the legacy activity logging environment variable', function () {
    $originalCurrentValue = getenv('ACTIVITYLOG_ENABLED');
    $originalLegacyValue = getenv('ACTIVITY_LOGGER_ENABLED');
    $hadCurrentEnvValue = array_key_exists('ACTIVITYLOG_ENABLED', $_ENV);
    $originalCurrentEnvValue = $_ENV['ACTIVITYLOG_ENABLED'] ?? null;
    $hadCurrentServerValue = array_key_exists('ACTIVITYLOG_ENABLED', $_SERVER);
    $originalCurrentServerValue = $_SERVER['ACTIVITYLOG_ENABLED'] ?? null;

    try {
        unset($_ENV['ACTIVITYLOG_ENABLED'], $_SERVER['ACTIVITYLOG_ENABLED']);
        putenv('ACTIVITYLOG_ENABLED');
        putenv('ACTIVITY_LOGGER_ENABLED=false');

        expect(require config_path('activitylog.php'))
            ->toHaveKey('enabled', false);
    } finally {
        if ($originalCurrentValue === false) {
            putenv('ACTIVITYLOG_ENABLED');
        } else {
            putenv('ACTIVITYLOG_ENABLED='.$originalCurrentValue);
        }

        if ($originalLegacyValue === false) {
            putenv('ACTIVITY_LOGGER_ENABLED');
        } else {
            putenv('ACTIVITY_LOGGER_ENABLED='.$originalLegacyValue);
        }

        if ($hadCurrentEnvValue) {
            $_ENV['ACTIVITYLOG_ENABLED'] = $originalCurrentEnvValue;
        } else {
            unset($_ENV['ACTIVITYLOG_ENABLED']);
        }

        if ($hadCurrentServerValue) {
            $_SERVER['ACTIVITYLOG_ENABLED'] = $originalCurrentServerValue;
        } else {
            unset($_SERVER['ACTIVITYLOG_ENABLED']);
        }
    }
});

it('stores model changes in attribute_changes after the upgrade', function () {
    $course = Curso::query()->create([
        'descricao' => 'Curso original',
        'tipo_curso' => 'OFICIAL',
    ]);

    $material = CursoMaterial::query()->create([
        'uid' => uniqid('MAT-'),
        'curso_id' => $course->id,
        'arquivo' => 'material-teste.pdf',
        'descricao' => 'Descrição original',
    ]);

    $material->update(['descricao' => 'Descrição atualizada']);

    $activity = SpatieActivity::query()
        ->where('subject_type', CursoMaterial::class)
        ->where('subject_id', $material->id)
        ->where('event', 'updated')
        ->firstOrFail();

    expect($activity->attribute_changes->get('attributes')['descricao'])->toBe('Descrição atualizada');
    expect($activity->attribute_changes->get('old')['descricao'])->toBe('Descrição original');
});

it('records only changed attributes and preserves both users during impersonation', function () {
    $impersonator = User::factory()->create();
    $impersonated = User::factory()->create();
    $course = Curso::query()->create(['descricao' => 'Curso original', 'tipo_curso' => 'OFICIAL']);
    $material = CursoMaterial::query()->create([
        'uid' => uniqid('MAT-'),
        'curso_id' => $course->id,
        'arquivo' => 'inalterado.pdf',
        'descricao' => 'Descrição original',
    ]);

    Route::middleware('web')->post('/teste-activitylog-impersonacao', function () use ($course, $material) {
        $course->update(['descricao' => 'Curso atualizado']);
        $material->update(['descricao' => 'Descrição atualizada']);

        return response()->noContent();
    });

    $this->actingAs($impersonated)
        ->withSession(['impersonator_id' => $impersonator->id])
        ->post('/teste-activitylog-impersonacao')
        ->assertNoContent();

    foreach ([$course, $material] as $model) {
        $activity = SpatieActivity::query()->where('subject_type', $model->getMorphClass())
            ->where('subject_id', $model->id)->where('event', 'updated')->latest('id')->firstOrFail();

        expect($activity->causer_id)->toBe($impersonated->id)
            ->and($activity->causer_type)->toBe($impersonated->getMorphClass())
            ->and($activity->properties->get('impersonator_id'))->toBe($impersonator->id)
            ->and($activity->description)->toContain((string) $impersonator->id)
            ->and($activity->attribute_changes->get('attributes'))->toHaveKey('descricao')
            ->and($activity->attribute_changes->get('attributes'))->not->toHaveKey('arquivo')
            ->and($activity->attribute_changes->get('attributes'))->not->toHaveKey('tipo_curso');
    }
});

it('records an activity without impersonation details when no web session is available', function () {
    $course = Curso::query()->create(['descricao' => 'Curso original', 'tipo_curso' => 'OFICIAL']);

    $course->update(['descricao' => 'Curso atualizado']);

    $activity = SpatieActivity::query()->where('subject_type', $course->getMorphClass())
        ->where('subject_id', $course->id)->where('event', 'updated')->latest('id')->firstOrFail();

    expect($activity->description)->toBe('updated')
        ->and($activity->properties?->get('impersonator_id'))->toBeNull()
        ->and($activity->attribute_changes->get('attributes'))->toHaveKey('descricao')
        ->and($activity->attribute_changes->get('attributes'))->not->toHaveKey('tipo_curso');
});

it('keeps the authenticated author and normal description without impersonation', function () {
    $user = User::factory()->create();
    $course = Curso::query()->create(['descricao' => 'Curso original', 'tipo_curso' => 'OFICIAL']);

    Route::middleware('web')->post('/teste-activitylog-sem-impersonacao', function () use ($course) {
        $course->update(['descricao' => 'Curso atualizado']);

        return response()->noContent();
    });

    $this->actingAs($user)->post('/teste-activitylog-sem-impersonacao')->assertNoContent();

    $activity = SpatieActivity::query()->where('subject_type', $course->getMorphClass())
        ->where('subject_id', $course->id)->where('event', 'updated')->latest('id')->firstOrFail();

    expect($activity->causer_id)->toBe($user->id)
        ->and($activity->description)->toBe('updated')
        ->and($activity->properties?->get('impersonator_id'))->toBeNull();
});

it('records the impersonator on creation and deletion events', function () {
    $impersonator = User::factory()->create();
    $impersonated = User::factory()->create();

    Route::middleware('web')->post('/teste-activitylog-criacao-exclusao', function () {
        $course = Curso::query()->create(['descricao' => 'Curso temporário', 'tipo_curso' => 'OFICIAL']);
        $course->delete();

        return response()->noContent();
    });

    $this->actingAs($impersonated)
        ->withSession(['impersonator_id' => $impersonator->id])
        ->post('/teste-activitylog-criacao-exclusao')
        ->assertNoContent();

    foreach (['created', 'deleted'] as $event) {
        $activity = SpatieActivity::query()->where('subject_type', (new Curso)->getMorphClass())
            ->where('event', $event)->latest('id')->firstOrFail();

        expect($activity->causer_id)->toBe($impersonated->id)
            ->and($activity->properties->get('impersonator_id'))->toBe($impersonator->id);
    }
});

it('preserves the impersonator id even when that user no longer exists', function () {
    $impersonated = User::factory()->create();
    $course = Curso::query()->create(['descricao' => 'Curso original', 'tipo_curso' => 'OFICIAL']);

    Route::middleware('web')->post('/teste-activitylog-impersonador-ausente', function () use ($course) {
        $course->update(['descricao' => 'Curso atualizado']);

        return response()->noContent();
    });

    $this->actingAs($impersonated)
        ->withSession(['impersonator_id' => 999999])
        ->post('/teste-activitylog-impersonador-ausente')
        ->assertNoContent();

    $activity = SpatieActivity::query()->where('subject_type', $course->getMorphClass())
        ->where('subject_id', $course->id)->where('event', 'updated')->latest('id')->firstOrFail();

    expect($activity->causer_id)->toBe($impersonated->id)
        ->and($activity->properties->get('impersonator_id'))->toBe(999999);
});

it('redirects guests from the activity log dashboard to login', function () {
    $this->get(route('activitylog-ui.dashboard'))->assertRedirect(route('login'));
});

it('allows an email on the activity log allow-list to view the dashboard', function () {
    config(['activitylog-ui.access.allowed_users' => ['auditor@example.com']]);
    $user = User::factory()->create(['email' => 'auditor@example.com']);

    $this->actingAs($user)
        ->get(route('activitylog-ui.dashboard'))
        ->assertOk();
});

it('forbids an authenticated user outside the activity log allow-list', function () {
    config(['activitylog-ui.access.allowed_users' => ['auditor@example.com']]);
    $user = User::factory()->create(['email' => 'other@example.com']);

    $this->actingAs($user)
        ->get(route('activitylog-ui.dashboard'))
        ->assertForbidden();
});

it('shows tracked changes from an activity in the activity log details API', function () {
    config(['activitylog-ui.access.allowed_users' => ['auditor@example.com']]);
    $user = User::factory()->create(['email' => 'auditor@example.com']);
    $activity = SpatieActivity::query()->create([
        'log_name' => 'test',
        'description' => 'Record updated',
        'event' => 'updated',
        'attribute_changes' => [
            'attributes' => ['name' => 'New name'],
            'old' => ['name' => 'Old name'],
        ],
        'properties' => ['source' => 'test'],
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('activitylog-ui.api.activities.show', $activity->id));

    $response->assertOk()
        ->assertJsonPath('data.attribute_changes.attributes.name', 'New name')
        ->assertJsonPath('data.attribute_changes.old.name', 'Old name');
});

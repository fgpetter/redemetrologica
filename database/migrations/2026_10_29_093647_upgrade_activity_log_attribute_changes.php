<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        [$connection, $table] = $this->activityLogTable();

        if (! Schema::connection($connection)->hasColumn($table, 'attribute_changes')) {
            Schema::connection($connection)->table($table, function ($table): void {
                $table->json('attribute_changes')->nullable()->after('causer_id');
            });
        }

        DB::connection($connection)->table($table)
            ->whereNull('attribute_changes')
            ->orderBy('id')
            ->chunkById(500, function ($activities) use ($connection, $table): void {
                foreach ($activities as $activity) {
                    if ($activity->properties === null) {
                        continue;
                    }

                    $decodedProperties = json_decode($activity->properties, flags: JSON_THROW_ON_ERROR);

                    if (! $decodedProperties instanceof stdClass) {
                        continue;
                    }

                    $properties = get_object_vars($decodedProperties);
                    $changes = array_intersect_key($properties, array_flip(['attributes', 'old']));

                    if ($changes === []) {
                        continue;
                    }

                    $remainingProperties = array_diff_key($properties, array_flip(['attributes', 'old']));

                    DB::connection($connection)->table($table)
                        ->where('id', $activity->id)
                        ->update([
                            'attribute_changes' => json_encode($changes, JSON_THROW_ON_ERROR),
                            'properties' => $remainingProperties === []
                                ? null
                                : json_encode((object) $remainingProperties, JSON_THROW_ON_ERROR),
                        ]);
                }
            });
    }

    public function down(): void
    {
        [$connection, $table] = $this->activityLogTable();

        if (! Schema::connection($connection)->hasColumn($table, 'attribute_changes')) {
            return;
        }

        $activities = DB::connection($connection)->table($table)
            ->whereNotNull('attribute_changes')
            ->orderBy('id');

        $activities->chunkById(500, function ($chunk): void {
            foreach ($chunk as $activity) {
                $properties = $this->decodeRollbackObject($activity->properties, 'properties', $activity->id);
                $changes = $this->decodeRollbackObject($activity->attribute_changes, 'attribute_changes', $activity->id);

                if (array_intersect_key($changes, $properties) !== []) {
                    throw new RuntimeException("Activity log rollback aborted: activity #{$activity->id} has overlapping properties and attribute_changes keys.");
                }
            }
        });

        DB::connection($connection)->table($table)
            ->whereNotNull('attribute_changes')
            ->orderBy('id')
            ->chunkById(500, function ($activities) use ($connection, $table): void {
                foreach ($activities as $activity) {
                    $properties = $activity->properties === null
                        ? []
                        : get_object_vars(json_decode($activity->properties, flags: JSON_THROW_ON_ERROR));
                    $changes = get_object_vars(json_decode($activity->attribute_changes, flags: JSON_THROW_ON_ERROR));

                    DB::connection($connection)->table($table)
                        ->where('id', $activity->id)
                        ->update([
                            'properties' => json_encode((object) array_replace($changes, $properties), JSON_THROW_ON_ERROR),
                        ]);
                }
            });

        Schema::connection($connection)->table($table, function ($table): void {
            $table->dropColumn('attribute_changes');
        });
    }

    /** @return array{0: string|null, 1: string} */
    private function activityLogTable(): array
    {
        $activityModelClass = config('activitylog.activity_model');
        $activityModel = new $activityModelClass;

        return [$activityModel->getConnectionName(), $activityModel->getTable()];
    }

    /** @return array<string, mixed> */
    private function decodeRollbackObject(?string $json, string $column, int $activityId): array
    {
        if ($json === null && $column === 'properties') {
            return [];
        }

        $decoded = $json === null ? null : json_decode($json, flags: JSON_THROW_ON_ERROR);

        if (! $decoded instanceof stdClass) {
            throw new RuntimeException("Activity log rollback aborted: activity #{$activityId} has non-object JSON in {$column}.");
        }

        return get_object_vars($decoded);
    }
};

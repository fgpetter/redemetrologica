<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

trait LogsModelActivity
{
    use LogsActivity;

    public function beforeActivityLogged(Model $activity, string $eventName): void
    {
        if (! app()->bound('request')) {
            return;
        }

        $request = app('request');

        if (! $request->hasSession()) {
            return;
        }

        $impersonatorId = $request->session()->get('impersonator_id');

        if (! $impersonatorId) {
            return;
        }

        $properties = collect($activity->properties ?? []);
        $properties->put('impersonator_id', $impersonatorId);
        $activity->properties = $properties;
        $activity->description = "{$activity->description} (impersonated by user #{$impersonatorId})";
    }
}

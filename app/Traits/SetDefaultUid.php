<?php

namespace App\Traits;

trait SetDefaultUid
{
  protected static function bootSetDefaultUid(): void
  {
    static::creating(function ($model) {
      if (empty($model->uid)) {
        $model->uid = uniqid();
      }
    });
  }
}
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, LogsActivity, Notifiable, SetDefaultUid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uid',
        'name',
        'email',
        'password',
        'email_verified_at',
        'temporary_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->dontLogIfAttributesChangedOnly(['remember_token'])
            ->useLogName('Usuários');

        if (session('impersonator_id')) {
            $impersonator = User::find(session('impersonator_id'));
            $options->setDescriptionForEvent(function (string $eventName) use ($impersonator) {
                return "{$eventName} impersonated by {$impersonator->name}";
            });
        }
    }

    /**
     * Carrega pessoa
     */
    public function pessoa(): HasOne
    {
        return $this->hasOne(Pessoa::class);
    }

    /**
     * Retorna as permissões do usuario
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Adiciona permissão ao usuario.
     *
     * @param  string  $permission  A permissão a ser adicionada
     */
    public function givePermission($permission): void
    {
        /** @var Permission $permission */
        $permission = Permission::firstOrCreate(['permission' => $permission]);

        $this->permissions()->attach($permission);
    }

    /**
     * Verifica se o usuário possui uma determinada permissão.
     *
     * @param  string|string[]  $permission  A permissão a ser verificada
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_string($permission)) {
            $permission = [$permission];
        }

        return $this->permissions()->whereIn('permission', $permission)->exists();
    }
}

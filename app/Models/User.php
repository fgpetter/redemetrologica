<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\SetDefaultUid;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\{HasOne,BelongsTo, BelongsToMany};

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity, SetDefaultUid;


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
        'temporary_password'
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
            $options->setDescriptionForEvent(function(string $eventName) use ($impersonator) {
                return "{$eventName} impersonated by {$impersonator->name}";
            });
        }
    }


    /**
     * Carrega pesoa
     * @return BelongsTo
     */
    public function pessoa(): HasOne
    {
        return $this->hasOne(Pessoa::class);
    }

    /**
     * Retorna as permissões do usuario
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Adiciona permissão ao usuario.
     *
     * @param string $permission A permissão a ser adicionada
     *
     * @return void
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
     * @param string|string[] $permission A permissão a ser verificada
     *
     * @return bool
     */
    public function hasPermissionTo($permission): bool
    {
        if(is_string($permission)){
            $permission = [$permission];
        }
        return $this->permissions()->whereIn('permission', $permission)->exists();
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Pessoa;
use App\Models\Permission;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\{HasOne,BelongsTo, BelongsToMany};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


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
        'avatar',
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

    /**
     * Carrega pesoa
     * @return BelongsTo
     */
    public function pessoa(): HasOne
    {
        return $this->hasOne(Pessoa::class);
    }

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
        return $this->permissions()->whereIn('permission', $permission)->exists();
    }
}

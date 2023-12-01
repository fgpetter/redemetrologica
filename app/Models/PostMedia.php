<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PostMedia extends Model
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['slug_post', 'caminho_media'];

    // /**
    //  * Carrega categorias
    //  * @return BelongsToMany
    //  */
    // public function categorias() : BelongsToMany
    // {
    //     return $this->belongsToMany(Category::class);
    // }
}

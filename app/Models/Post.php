<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id', 'titulo', 'slug', 'conteudo', 'thumb', 'rascunho', 'tipo', 'data_publicacao'];

    // /**
    //  * Carrega categorias
    //  * @return BelongsToMany
    //  */
    // public function categorias() : BelongsToMany
    // {
    //     return $this->belongsToMany(Category::class);
    // }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('categories_posts');
        Schema::dropIfExists('post_media');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('slug');
            $table->text('conteudo')->nullable();
            $table->string('thumb')->nullable();
            $table->boolean('rascunho')->default(false);
            $table->enum('tipo', ['noticia', 'galeria']);
            $table->date('data_publicacao');
            $table->timestamps();
        });

        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->text('slug_post');
            $table->text('caminho_media')->nullable();
            $table->timestamps();
        });

        Schema::create('categories_posts', function (Blueprint $table) {
            $table->foreignId('category_id');
            $table->foreignId('post_id');
        });
    }
};

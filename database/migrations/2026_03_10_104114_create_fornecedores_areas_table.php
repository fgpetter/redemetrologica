<?php

use App\Enums\FornecedorArea;
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
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->date('fornecedor_desde')->nullable()->after('pessoa_id');
            $table->boolean('ativo')->default(true)->after('observacoes');
        });

        Schema::create('fornecedores_areas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('fornecedor_id')->constrained('fornecedores', 'id')->onDelete('cascade');
            $table->enum('area', FornecedorArea::values());
            $table->string('atuacao')->nullable();
            $table->string('pessoa_contato')->nullable();
            $table->string('pessoa_contato_email')->nullable();
            $table->string('pessoa_contato_telefone')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->dropColumn('fornecedor_desde');
        });
        Schema::dropIfExists('fornecedores_areas');
    }
};

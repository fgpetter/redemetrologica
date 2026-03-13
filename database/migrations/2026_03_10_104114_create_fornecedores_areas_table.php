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
        });

        Schema::create('fornecedores_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fornecedor_id')->constrained()->onDelete('cascade');
            $table->enum('area', FornecedorArea::cases());
            $table->string('atuacao')->nullable();
            $table->string('pessoa_contato')->nullable();
            $table->string('pessoa_contato_email')->nullable();
            $table->string('pessoa_contato_telefone')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('fornecedores_areas');
    }
};

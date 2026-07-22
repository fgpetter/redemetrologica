<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('interlab_despesa_lancamentos')) {
            Schema::create('interlab_despesa_lancamentos', function (Blueprint $table) {
                $table->id();
                $table->string('uid')->unique();
                $table->foreignId('agenda_interlab_id')->constrained('agenda_interlabs')->cascadeOnDelete();
                $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasColumn('interlab_despesas', 'interlab_despesa_lancamento_id')) {
            Schema::table('interlab_despesas', function (Blueprint $table) {
                $table->foreignId('interlab_despesa_lancamento_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('interlab_despesa_lancamentos')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('fornecedores_avaliacao', 'interlab_despesa_lancamento_id')) {
            Schema::table('fornecedores_avaliacao', function (Blueprint $table) {
                $table->foreignId('interlab_despesa_lancamento_id')
                    ->nullable()
                    ->after('uid')
                    ->constrained('interlab_despesa_lancamentos')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('interlab_despesas', 'agenda_interlab_id')) {
            $this->backfillLancamentosEItens();
        }

        if (Schema::hasColumn('fornecedores_avaliacao', 'agenda_interlab_id')) {
            $this->backfillAvaliacoes();
        }

        if (Schema::hasColumn('interlab_despesas', 'agenda_interlab_id')) {
            Schema::table('interlab_despesas', function (Blueprint $table) {
                $table->dropForeign(['agenda_interlab_id']);
                $table->dropForeign(['fornecedor_id']);
                $table->dropColumn(['agenda_interlab_id', 'fornecedor_id']);
            });
        }

        if (Schema::hasColumn('fornecedores_avaliacao', 'agenda_interlab_id')) {
            Schema::table('fornecedores_avaliacao', function (Blueprint $table) {
                // MySQL exige remover a FK antes do índice único que a referencia.
                $table->dropForeign(['agenda_interlab_id']);
                $table->dropUnique(['agenda_interlab_id', 'fornecedor_id']);
                $table->dropColumn(['agenda_interlab_id']);
            });
        }

        $jaTemUniqueLancamento = collect(Schema::getIndexes('fornecedores_avaliacao'))
            ->contains(fn (array $index) => ($index['unique'] ?? false)
                && ($index['columns'] ?? []) === ['interlab_despesa_lancamento_id']);

        if (! $jaTemUniqueLancamento) {
            Schema::table('fornecedores_avaliacao', function (Blueprint $table) {
                $table->unique('interlab_despesa_lancamento_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fornecedores_avaliacao', function (Blueprint $table) {
            $table->dropUnique(['interlab_despesa_lancamento_id']);
            $table->foreignId('agenda_interlab_id')
                ->nullable()
                ->after('uid')
                ->constrained('agenda_interlabs')
                ->cascadeOnDelete();
        });

        foreach (DB::table('fornecedores_avaliacao')->orderBy('id')->get() as $avaliacao) {
            $lancamento = DB::table('interlab_despesa_lancamentos')
                ->where('id', $avaliacao->interlab_despesa_lancamento_id)
                ->first();

            if ($lancamento) {
                DB::table('fornecedores_avaliacao')
                    ->where('id', $avaliacao->id)
                    ->update(['agenda_interlab_id' => $lancamento->agenda_interlab_id]);
            }
        }

        Schema::table('fornecedores_avaliacao', function (Blueprint $table) {
            $table->dropForeign(['interlab_despesa_lancamento_id']);
            $table->dropColumn('interlab_despesa_lancamento_id');
            $table->unique(['agenda_interlab_id', 'fornecedor_id']);
        });

        Schema::table('interlab_despesas', function (Blueprint $table) {
            $table->foreignId('agenda_interlab_id')
                ->nullable()
                ->after('uid')
                ->constrained('agenda_interlabs')
                ->cascadeOnDelete();
            $table->foreignId('fornecedor_id')
                ->nullable()
                ->after('agenda_interlab_id')
                ->constrained('fornecedores');
        });

        foreach (DB::table('interlab_despesas')->orderBy('id')->get() as $item) {
            $lancamento = DB::table('interlab_despesa_lancamentos')
                ->where('id', $item->interlab_despesa_lancamento_id)
                ->first();

            if ($lancamento) {
                DB::table('interlab_despesas')
                    ->where('id', $item->id)
                    ->update([
                        'agenda_interlab_id' => $lancamento->agenda_interlab_id,
                        'fornecedor_id' => $lancamento->fornecedor_id,
                    ]);
            }
        }

        Schema::table('interlab_despesas', function (Blueprint $table) {
            $table->dropForeign(['interlab_despesa_lancamento_id']);
            $table->dropColumn('interlab_despesa_lancamento_id');
        });

        Schema::dropIfExists('interlab_despesa_lancamentos');
    }

    private function backfillLancamentosEItens(): void
    {
        $grupos = DB::table('interlab_despesas')
            ->select('agenda_interlab_id', 'fornecedor_id')
            ->whereNotNull('agenda_interlab_id')
            ->whereNotNull('fornecedor_id')
            ->whereNull('interlab_despesa_lancamento_id')
            ->groupBy('agenda_interlab_id', 'fornecedor_id')
            ->get();

        foreach ($grupos as $grupo) {
            $lancamentoId = DB::table('interlab_despesa_lancamentos')
                ->where('agenda_interlab_id', $grupo->agenda_interlab_id)
                ->where('fornecedor_id', $grupo->fornecedor_id)
                ->value('id');

            if ($lancamentoId === null) {
                $now = now();
                $lancamentoId = DB::table('interlab_despesa_lancamentos')->insertGetId([
                    'uid' => Str::lower(Str::random(13)),
                    'agenda_interlab_id' => $grupo->agenda_interlab_id,
                    'fornecedor_id' => $grupo->fornecedor_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('interlab_despesas')
                ->where('agenda_interlab_id', $grupo->agenda_interlab_id)
                ->where('fornecedor_id', $grupo->fornecedor_id)
                ->whereNull('interlab_despesa_lancamento_id')
                ->update(['interlab_despesa_lancamento_id' => $lancamentoId]);
        }
    }

    private function backfillAvaliacoes(): void
    {
        foreach (DB::table('fornecedores_avaliacao')->whereNull('interlab_despesa_lancamento_id')->orderBy('id')->get() as $avaliacao) {
            $lancamentoId = DB::table('interlab_despesa_lancamentos')
                ->where('agenda_interlab_id', $avaliacao->agenda_interlab_id)
                ->where('fornecedor_id', $avaliacao->fornecedor_id)
                ->value('id');

            if ($lancamentoId === null) {
                $now = now();
                $lancamentoId = DB::table('interlab_despesa_lancamentos')->insertGetId([
                    'uid' => Str::lower(Str::random(13)),
                    'agenda_interlab_id' => $avaliacao->agenda_interlab_id,
                    'fornecedor_id' => $avaliacao->fornecedor_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('fornecedores_avaliacao')
                ->where('id', $avaliacao->id)
                ->update(['interlab_despesa_lancamento_id' => $lancamentoId]);
        }
    }
};

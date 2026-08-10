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
        Schema::create('disciplina_professor_conflitos_quarentena', function (Blueprint $table) {
            $table->unsignedBigInteger('id_disciplina_professor')->primary();
            $table->unsignedBigInteger('professor_id');
            $table->unsignedBigInteger('disciplina_id');
            $table->unsignedBigInteger('turma_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('disciplina_canonica_id');
            $table->string('criterio_canonico', 50);
            $table->string('especialidade_professor')->nullable();
            $table->string('motivo_quarentena');
            $table->timestamp('quarentena_em');

            $table->index('professor_id', 'dp_conflitos_professor_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplina_professor_conflitos_quarentena');
    }
};

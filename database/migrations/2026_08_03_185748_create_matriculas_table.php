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
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id('id_matricula');
            $table->foreignId('aluno_id')->references('id_aluno')->on('alunos');
            $table->foreignId('turma_id')->references('id_turma')->on('turmas');
            $table->date('data_matricula');
            $table->year('ano_letivo');
            $table->enum('situacao', ['ativa', 'trancada', 'cancelada', 'transferida', 'concluida'])->default('ativa');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};

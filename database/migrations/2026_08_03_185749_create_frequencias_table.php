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
        Schema::create('frequencias', function (Blueprint $table) {
            $table->id('id_frequencia');
            $table->foreignId('aluno_id')->references('id_aluno')->on('alunos');
            $table->foreignId('disciplina_id')->references('id_disciplina')->on('disciplinas');
            $table->foreignId('turma_id')->references('id_turma')->on('turmas');
            $table->foreignId('professor_id')->references('id_professor')->on('professores');
            $table->date('data_aula');
            $table->enum('situacao', ['presente', 'ausente', 'justificada', 'atrasado'])->default('presente');
            $table->text('justificativa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frequencias');
    }
};

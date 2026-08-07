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
        Schema::create('notas', function (Blueprint $table) {
            $table->id('id_nota');
            $table->foreignId('aluno_id')->references('id_aluno')->on('alunos');
            $table->foreignId('disciplina_id')->references('id_disciplina')->on('disciplinas');
            $table->foreignId('turma_id')->references('id_turma')->on('turmas');
            $table->foreignId('professor_id')->references('id_professor')->on('professores');
            $table->enum('periodo', ['primeiro_bimestre', 'segundo_bimestre', 'terceiro_bimestre', 'quarto_bimestre']);
            $table->string('avaliacao', 100);
            $table->decimal('valor', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};

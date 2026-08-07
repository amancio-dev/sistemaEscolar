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
        Schema::create('disciplina_professor', function (Blueprint $table) {
            $table->id('id_disciplina_professor');
            $table->foreignId('professor_id')->references('id_professor')->on('professores');
            $table->foreignId('disciplina_id')->references('id_disciplina')->on('disciplinas');
            $table->foreignId('turma_id')->references('id_turma')->on('turmas');
            $table->unique(['professor_id', 'disciplina_id', 'turma_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplina_professor');
    }
};

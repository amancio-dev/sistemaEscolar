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
        Schema::create('turmas', function (Blueprint $table) {
            $table->id('id_turma');
            $table->string('nome');
            $table->string('serie', 50);
            $table->enum('turno', ['matutino', 'vespertino', 'noturno', 'integral']);
            $table->string('sala', 50);
            $table->year('ano_letivo');
            $table->unsignedInteger('limite_alunos');
            $table->foreignId('professor_responsavel_id')->nullable()->references('id_professor')->on('professores')->nullOnDelete();
            $table->string('situacao')->default('ativa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turmas');
    }
};

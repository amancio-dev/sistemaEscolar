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
        Schema::create('alunos', function (Blueprint $table) {
            $table->id('id_aluno');
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('numero_matricula')->unique();
            $table->string('nome');
            $table->string('cpf', 14)->unique();
            $table->date('data_nascimento');
            $table->string('telefone', 20)->nullable();
            $table->string('email')->nullable()->unique();
            $table->text('endereco')->nullable();
            $table->string('nome_responsavel')->nullable();
            $table->string('telefone_responsavel', 20)->nullable();
            $table->string('situacao')->default('ativo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};

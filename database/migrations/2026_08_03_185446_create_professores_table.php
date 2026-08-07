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
        Schema::create('professores', function (Blueprint $table) {
            $table->id('id_professor');
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nome');
            $table->string('cpf', 14)->unique();
            $table->string('telefone', 20)->nullable();
            $table->string('email')->unique();
            $table->text('endereco')->nullable();
            $table->string('formacao')->nullable();
            $table->string('especialidade')->nullable();
            $table->date('data_contratacao')->nullable();
            $table->string('situacao')->default('ativo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professores');
    }
};

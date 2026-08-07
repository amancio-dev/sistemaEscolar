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
        Schema::create('disciplinas', function (Blueprint $table) {
            $table->id('id_disciplina');
            $table->string('nome');
            $table->string('codigo', 50)->unique();
            $table->text('descricao')->nullable();
            $table->unsignedInteger('carga_horaria');
            $table->decimal('media_minima', 4, 2)->default(6.00);
            $table->string('situacao')->default('ativa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinas');
    }
};

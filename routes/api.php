<?php

use App\Http\Controllers\AlocacaoController;
use App\Http\Controllers\AlunoController;
use App\Http\Controllers\DisciplinaController;
use App\Http\Controllers\FrequenciaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\TurmaController;
use Illuminate\Support\Facades\Route;

// A API usa a mesma sessão autenticada do portal e, por isso, inclui o grupo web.
Route::middleware(['web', 'auth:web', 'active', 'role:administrador,professor'])->name('api.')->group(function (): void {
    Route::get('turmas', [TurmaController::class, 'index'])->name('turmas.index');
    Route::get('turmas/{turma}', [TurmaController::class, 'show'])->name('turmas.show');
    Route::apiResources([
        'notas' => NotaController::class,
        'frequencias' => FrequenciaController::class,
    ]);
});

Route::middleware(['web', 'auth:web', 'active', 'role:administrador'])->name('api.')->group(function (): void {
    Route::apiResource('turmas', TurmaController::class)->except(['index', 'show']);
    Route::apiResources([
        'alunos' => AlunoController::class,
        'professores' => ProfessorController::class,
        'disciplinas' => DisciplinaController::class,
        'alocacoes' => AlocacaoController::class,
        'matriculas' => MatriculaController::class,
    ]);
});

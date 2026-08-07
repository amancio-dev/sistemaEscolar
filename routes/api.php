<?php

use App\Http\Controllers\AlunoController;
use App\Http\Controllers\DisciplinaController;
use App\Http\Controllers\FrequenciaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\TurmaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:administrador,professor'])->name('api.')->group(function (): void {
    Route::apiResources([
        'alunos' => AlunoController::class,
        'professores' => ProfessorController::class,
        'turmas' => TurmaController::class,
        'disciplinas' => DisciplinaController::class,
        'matriculas' => MatriculaController::class,
        'notas' => NotaController::class,
        'frequencias' => FrequenciaController::class,
    ]);
});

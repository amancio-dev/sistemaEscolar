<?php

use App\Http\Controllers\AlunoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisciplinaController;
use App\Http\Controllers\FrequenciaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TurmaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/entrar', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/entrar', [AuthController::class, 'login'])->name('login.store');
    Route::get('/cadastrar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/cadastrar', [AuthController::class, 'register'])->name('register.store');

    Route::get('/esqueci-senha', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/esqueci-senha', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/redefinir-senha/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/redefinir-senha', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::post('/sair', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('inicio');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/perfil/senha', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::resources([
        'alunos' => AlunoController::class,
        'professores' => ProfessorController::class,
        'turmas' => TurmaController::class,
        'disciplinas' => DisciplinaController::class,
        'matriculas' => MatriculaController::class,
        'notas' => NotaController::class,
        'frequencias' => FrequenciaController::class,
    ], ['except' => ['show']]);
});

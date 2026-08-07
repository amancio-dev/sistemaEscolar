<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Professor;
use App\Models\Turma;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        return view('dashboard.index', [
            'totals' => [
                'alunos' => Aluno::query()->count(),
                'professores' => Professor::query()->count(),
                'turmas' => Turma::query()->count(),
                'disciplinas' => Disciplina::query()->count(),
                'matriculas' => Matricula::query()->count(),
                'notas' => Nota::query()->count(),
                'frequencias' => Frequencia::query()->count(),
            ],

            'activeStudents' => Aluno::query()->where('situacao', 'ativo')->count(),
            'activeProfessors' => Professor::query()->where('situacao', 'ativo')->count(),
            'activeEnrollments' => Matricula::query()->where('situacao', 'ativa')->count(),
            'activeTurmas' => Turma::query()->where('situacao', 'ativa')->count(),

            'todayAttendance' => [
                'presente' => Frequencia::query()->where('data_aula', $today)->where('situacao', 'presente')->count(),
                'ausente' => Frequencia::query()->where('data_aula', $today)->where('situacao', 'ausente')->count(),
                'total' => Frequencia::query()->where('data_aula', $today)->count(),
            ],

            'gradeStats' => [
                'average' => Nota::query()->avg('valor'),
                'above' => Nota::query()->where('valor', '>=', 6)->count(),
                'below' => Nota::query()->where('valor', '<', 6)->count(),
            ],

            'recentStudents' => Aluno::query()
                ->latest('id_aluno')
                ->limit(6)
                ->get(),

            'recentProfessors' => Professor::query()
                ->latest('id_professor')
                ->limit(4)
                ->get(),

            'recentEnrollments' => Matricula::query()
                ->with(['aluno', 'turma'])
                ->latest('id_matricula')
                ->limit(5)
                ->get(),
        ]);
    }
}

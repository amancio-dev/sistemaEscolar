<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Professor;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->user()->isAluno()) {
            return $this->painelDoAluno($request->user());
        }

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

    private function painelDoAluno(User $user): View
    {
        $aluno = $user->aluno()
            ->with(['matriculas' => fn ($query) => $query->with('turma')->latest('data_matricula')])
            ->first();

        if (! $aluno) {
            return view('dashboard.aluno', [
                'aluno' => null,
                'matriculaAtiva' => null,
                'gradeStats' => null,
                'attendanceStats' => null,
                'frequenciaPercentual' => null,
                'recentNotes' => collect(),
                'recentAttendance' => collect(),
            ]);
        }

        $gradeStats = $aluno->notas()
            ->selectRaw('COUNT(*) as total, AVG(valor) as media, MIN(valor) as menor, MAX(valor) as maior')
            ->first();

        $attendanceStats = $aluno->frequencias()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN situacao = 'presente' THEN 1 ELSE 0 END) as presencas")
            ->selectRaw("SUM(CASE WHEN situacao = 'ausente' THEN 1 ELSE 0 END) as faltas")
            ->selectRaw("SUM(CASE WHEN situacao = 'justificada' THEN 1 ELSE 0 END) as justificadas")
            ->selectRaw("SUM(CASE WHEN situacao = 'atrasado' THEN 1 ELSE 0 END) as atrasos")
            ->first();

        $frequenciaPercentual = (int) $attendanceStats->total > 0
            ? round((((int) $attendanceStats->presencas + (int) $attendanceStats->atrasos) / (int) $attendanceStats->total) * 100, 1)
            : null;

        return view('dashboard.aluno', [
            'aluno' => $aluno,
            'matriculaAtiva' => $aluno->matriculas->firstWhere('situacao', 'ativa') ?? $aluno->matriculas->first(),
            'gradeStats' => $gradeStats,
            'attendanceStats' => $attendanceStats,
            'frequenciaPercentual' => $frequenciaPercentual,
            'recentNotes' => $aluno->notas()->with(['disciplina', 'turma'])->latest('id_nota')->limit(5)->get(),
            'recentAttendance' => $aluno->frequencias()->with(['disciplina', 'turma'])
                ->orderByDesc('data_aula')->orderByDesc('id_frequencia')->limit(6)->get(),
        ]);
    }
}

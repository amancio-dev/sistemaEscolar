<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Frequencia;
use App\Models\Nota;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    public function notas(Request $request): View
    {
        $aluno = $this->alunoAutenticado($request);
        $search = trim((string) $request->query('busca', ''));
        $periodo = (string) $request->query('periodo', '');
        $periodosValidos = [
            'primeiro_bimestre',
            'segundo_bimestre',
            'terceiro_bimestre',
            'quarto_bimestre',
        ];

        $query = Nota::query()
            ->with(['disciplina', 'turma'])
            ->when($aluno, fn (Builder $query) => $query->where('aluno_id', $aluno->getKey()))
            ->when(! $aluno, fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->when(in_array($periodo, $periodosValidos, true), fn (Builder $query) => $query->where('periodo', $periodo))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('avaliacao', 'like', "%{$search}%")
                        ->orWhereHas('disciplina', fn (Builder $relation) => $relation
                            ->where('nome', 'like', "%{$search}%")
                            ->orWhere('codigo', 'like', "%{$search}%"));
                });
            });

        $summary = (clone $query)
            ->withoutEagerLoads()
            ->reorder()
            ->selectRaw('COUNT(*) as total, AVG(valor) as media, MIN(valor) as menor, MAX(valor) as maior')
            ->first();

        $records = $query
            ->orderByDesc('id_nota')
            ->paginate(10)
            ->withQueryString();

        return view('portal.notas', compact('aluno', 'records', 'search', 'periodo', 'summary'));
    }

    public function frequencias(Request $request): View
    {
        $aluno = $this->alunoAutenticado($request);
        $filters = [
            'busca' => trim((string) $request->query('busca', '')),
            'situacao' => (string) $request->query('situacao', ''),
            'data_inicio' => (string) $request->query('data_inicio', ''),
            'data_fim' => (string) $request->query('data_fim', ''),
        ];

        $query = Frequencia::query()
            ->with(['disciplina', 'turma'])
            ->when($aluno, fn (Builder $query) => $query->where('aluno_id', $aluno->getKey()))
            ->when(! $aluno, fn (Builder $query) => $query->whereRaw('1 = 0'));

        $this->aplicarFiltrosDeFrequencia($query, $filters);

        $totals = (clone $query)
            ->withoutEagerLoads()
            ->reorder()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN situacao = 'presente' THEN 1 ELSE 0 END) as presencas")
            ->selectRaw("SUM(CASE WHEN situacao = 'ausente' THEN 1 ELSE 0 END) as faltas")
            ->selectRaw("SUM(CASE WHEN situacao = 'justificada' THEN 1 ELSE 0 END) as justificadas")
            ->selectRaw("SUM(CASE WHEN situacao = 'atrasado' THEN 1 ELSE 0 END) as atrasos")
            ->first();

        $resumoPorDisciplina = (clone $query)
            ->withoutEagerLoads()
            ->reorder()
            ->select('disciplina_id')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN situacao = 'presente' THEN 1 ELSE 0 END) as presencas")
            ->selectRaw("SUM(CASE WHEN situacao = 'ausente' THEN 1 ELSE 0 END) as faltas")
            ->selectRaw("SUM(CASE WHEN situacao = 'justificada' THEN 1 ELSE 0 END) as justificadas")
            ->selectRaw("SUM(CASE WHEN situacao = 'atrasado' THEN 1 ELSE 0 END) as atrasos")
            ->groupBy('disciplina_id')
            ->with('disciplina')
            ->get()
            ->sortBy(fn (Frequencia $item) => $item->disciplina?->nome ?? '');

        $records = $query
            ->orderByDesc('data_aula')
            ->orderByDesc('id_frequencia')
            ->paginate(10)
            ->withQueryString();

        $frequenciaPercentual = (int) ($totals->total ?? 0) > 0
            ? round((((int) $totals->presencas + (int) $totals->atrasos) / (int) $totals->total) * 100, 1)
            : null;

        return view('portal.frequencias', compact(
            'aluno',
            'records',
            'filters',
            'totals',
            'resumoPorDisciplina',
            'frequenciaPercentual',
        ));
    }

    private function alunoAutenticado(Request $request): ?Aluno
    {
        return $request->user()->aluno()->first();
    }

    /** @param array{busca: string, situacao: string, data_inicio: string, data_fim: string} $filters */
    private function aplicarFiltrosDeFrequencia(Builder $query, array $filters): void
    {
        $situacoes = ['presente', 'ausente', 'justificada', 'atrasado'];

        $query
            ->when(in_array($filters['situacao'], $situacoes, true),
                fn (Builder $query) => $query->where('situacao', $filters['situacao']))
            ->when($filters['data_inicio'] !== '',
                fn (Builder $query) => $query->whereDate('data_aula', '>=', $filters['data_inicio']))
            ->when($filters['data_fim'] !== '',
                fn (Builder $query) => $query->whereDate('data_aula', '<=', $filters['data_fim']))
            ->when($filters['busca'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['busca'];
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('disciplina', fn (Builder $relation) => $relation
                        ->where('nome', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%"))
                        ->orWhereHas('turma', fn (Builder $relation) => $relation
                            ->where('nome', 'like', "%{$search}%"));
                });
            });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Frequencia;
use App\Models\Professor;
use App\Models\Turma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FrequenciaController extends CrudController
{
    protected string $modelClass = Frequencia::class;

    protected string $primaryKey = 'id_frequencia';

    protected array $searchable = ['situacao', 'data_aula'];

    protected string $singularLabel = 'registro de frequência';

    protected string $pluralLabel = 'Registros de frequências';

    protected string $resource = 'frequencias';

    protected array $with = ['aluno', 'disciplina', 'turma', 'professor'];

    protected array $relationSearches = [
        'aluno' => ['nome', 'numero_matricula'],
        'disciplina' => ['nome', 'codigo'],
        'turma' => ['nome'],
        'professor' => ['nome'],
    ];

    public function index(Request $request): JsonResponse|View
    {
        $perPage = min(max($request->integer('per_page', 10), 1), 100);
        $filters = [
            'busca' => trim((string) $request->query('busca', '')),
            'aluno_id' => $request->integer('aluno_id'),
            'disciplina_id' => $request->integer('disciplina_id'),
            'turma_id' => $request->integer('turma_id'),
            'situacao' => (string) $request->query('situacao', ''),
            'data_inicio' => (string) $request->query('data_inicio', ''),
            'data_fim' => (string) $request->query('data_fim', ''),
        ];

        $query = Frequencia::query()->with($this->with);
        $this->aplicarFiltros($query, $filters);

        $totals = (clone $query)
            ->withoutEagerLoads()
            ->reorder()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN situacao = 'presente' THEN 1 ELSE 0 END) as presencas")
            ->selectRaw("SUM(CASE WHEN situacao = 'ausente' THEN 1 ELSE 0 END) as faltas")
            ->selectRaw("SUM(CASE WHEN situacao = 'justificada' THEN 1 ELSE 0 END) as justificadas")
            ->selectRaw("SUM(CASE WHEN situacao = 'atrasado' THEN 1 ELSE 0 END) as atrasos")
            ->first();

        $resumoPorAluno = (clone $query)
            ->withoutEagerLoads()
            ->reorder()
            ->select('aluno_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN situacao = 'presente' THEN 1 ELSE 0 END) as presencas")
            ->selectRaw("SUM(CASE WHEN situacao = 'ausente' THEN 1 ELSE 0 END) as faltas")
            ->selectRaw("SUM(CASE WHEN situacao = 'justificada' THEN 1 ELSE 0 END) as justificadas")
            ->selectRaw("SUM(CASE WHEN situacao = 'atrasado' THEN 1 ELSE 0 END) as atrasos")
            ->groupBy('aluno_id')
            ->with('aluno')
            ->get()
            ->sortBy(fn (Frequencia $item) => $item->aluno?->nome ?? '');

        $records = $query
            ->orderByDesc('data_aula')
            ->orderByDesc($this->primaryKey)
            ->paginate($perPage)
            ->withQueryString();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => "{$this->pluralLabel} listados com sucesso.",
                'data' => $records,
                'resumo' => $totals,
            ]);
        }

        $frequenciaPercentual = (int) ($totals->total ?? 0) > 0
            ? round((((int) $totals->presencas + (int) $totals->atrasos) / (int) $totals->total) * 100, 1)
            : null;

        return view('frequencias.index', [
            'records' => $records,
            'search' => $filters['busca'],
            'filters' => $filters,
            'totals' => $totals,
            'resumoPorAluno' => $resumoPorAluno,
            'frequenciaPercentual' => $frequenciaPercentual,
            ...$this->formData(),
        ]);
    }

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';

        $registroUnico = Rule::unique('frequencias', 'data_aula')
            ->where(fn ($query) => $query
                ->where('aluno_id', request('aluno_id'))
                ->where('disciplina_id', request('disciplina_id')))
            ->ignore($id, 'id_frequencia');

        return [
            'aluno_id' => [$prefix, 'integer', 'exists:alunos,id_aluno'],
            'disciplina_id' => [$prefix, 'integer', 'exists:disciplinas,id_disciplina'],
            'turma_id' => [$prefix, 'integer', 'exists:turmas,id_turma'],
            'professor_id' => [$prefix, 'integer', 'exists:professores,id_professor'],
            'data_aula' => [$prefix, 'date', 'before_or_equal:today', $registroUnico],
            'situacao' => [$prefix, Rule::in(['presente', 'ausente', 'justificada', 'atrasado'])],
            'justificativa' => ['nullable', 'required_if:situacao,justificada', 'string', 'max:1000'],
        ];
    }

    protected function formData(): array
    {
        return [
            'alunos' => Aluno::query()->orderBy('nome')->get(),
            'disciplinas' => Disciplina::query()->orderBy('nome')->get(),
            'turmas' => Turma::query()->orderBy('nome')->get(),
            'professores' => Professor::query()->orderBy('nome')->get(),
        ];
    }

    protected function messages(): array
    {
        return [
            'data_aula.unique' => 'Já existe uma frequência para este aluno, disciplina e data.',
            'data_aula.before_or_equal' => 'A data da aula não pode estar no futuro.',
            'justificativa.required_if' => 'Informe a justificativa para uma falta justificada.',
        ];
    }

    protected function attributes(): array
    {
        return [
            'aluno_id' => 'aluno',
            'disciplina_id' => 'disciplina',
            'turma_id' => 'turma',
            'professor_id' => 'professor',
            'data_aula' => 'data da aula',
            'situacao' => 'situação',
            'justificativa' => 'justificativa',
        ];
    }

    /** @param array<string, int|string> $filters */
    private function aplicarFiltros(Builder $query, array $filters): void
    {
        $situacoes = ['presente', 'ausente', 'justificada', 'atrasado'];

        $query
            ->when($filters['aluno_id'] > 0,
                fn (Builder $query) => $query->where('aluno_id', $filters['aluno_id']))
            ->when($filters['disciplina_id'] > 0,
                fn (Builder $query) => $query->where('disciplina_id', $filters['disciplina_id']))
            ->when($filters['turma_id'] > 0,
                fn (Builder $query) => $query->where('turma_id', $filters['turma_id']))
            ->when(in_array($filters['situacao'], $situacoes, true),
                fn (Builder $query) => $query->where('situacao', $filters['situacao']))
            ->when($filters['data_inicio'] !== '',
                fn (Builder $query) => $query->whereDate('data_aula', '>=', $filters['data_inicio']))
            ->when($filters['data_fim'] !== '',
                fn (Builder $query) => $query->whereDate('data_aula', '<=', $filters['data_fim']))
            ->when($filters['busca'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['busca'];

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('situacao', 'like', "%{$search}%")
                        ->orWhere('data_aula', 'like', "%{$search}%")
                        ->orWhereHas('aluno', fn (Builder $relation) => $relation
                            ->where('nome', 'like', "%{$search}%")
                            ->orWhere('numero_matricula', 'like', "%{$search}%"))
                        ->orWhereHas('disciplina', fn (Builder $relation) => $relation
                            ->where('nome', 'like', "%{$search}%")
                            ->orWhere('codigo', 'like', "%{$search}%"))
                        ->orWhereHas('turma', fn (Builder $relation) => $relation
                            ->where('nome', 'like', "%{$search}%"))
                        ->orWhereHas('professor', fn (Builder $relation) => $relation
                            ->where('nome', 'like', "%{$search}%"));
                });
            });
    }
}

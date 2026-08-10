<?php

namespace App\Http\Controllers;

use App\Http\Resources\TurmaResource;
use App\Models\Professor;
use App\Models\Turma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TurmaController extends CrudController
{
    protected string $modelClass = Turma::class;

    protected string $primaryKey = 'id_turma';

    protected array $searchable = ['nome', 'serie', 'turno', 'sala', 'ano_letivo', 'situacao'];

    protected string $singularLabel = 'registro de turma';

    protected string $pluralLabel = 'Registros de turmas';

    protected string $resource = 'turmas';

    protected array $with = ['professorResponsavel:id_professor,nome'];

    protected array $relationSearches = [
        'professorResponsavel' => ['nome'],
    ];

    public function show(Request $request, int $id): JsonResponse|View
    {
        $query = Turma::query()
            ->with([
                'professorResponsavel',
                'alocacoes.professor',
                'alocacoes.disciplina',
                'matriculas' => fn ($query) => $query
                    ->where('situacao', 'ativa')
                    ->whereHas('aluno', fn ($query) => $query->where('situacao', 'ativo'))
                    ->with('aluno'),
            ])
            ->withCount([
                'matriculas as matriculas_ativas_count' => fn ($query) => $query
                    ->where('situacao', 'ativa')
                    ->whereHas('aluno', fn ($query) => $query->where('situacao', 'ativo')),
            ]);

        $this->applyIndexScope($query, $request);
        $turma = $query->findOrFail($id);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Registro de turma consultado com sucesso.',
                'data' => (new TurmaResource($turma))->resolve($request),
            ]);
        }

        $turma->setRelation(
            'matriculas',
            $turma->matriculas
                ->sortBy(fn ($matricula): string => $matricula->aluno?->nome ?? '', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
        );

        return view('turmas.show', [
            'turma' => $turma,
            'vagasDisponiveis' => max(
                0,
                (int) $turma->limite_alunos - (int) $turma->matriculas_ativas_count,
            ),
        ]);
    }

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';

        return [
            'nome' => [$prefix, 'string', 'max:255'],
            'serie' => [$prefix, 'string', 'max:50'],
            'turno' => [$prefix, Rule::in(['matutino', 'vespertino', 'noturno', 'integral'])],
            'sala' => [$prefix, 'string', 'max:50'],
            'ano_letivo' => [$prefix, 'digits:4'],
            'limite_alunos' => [$prefix, 'integer', 'min:1', 'max:200'],
            'professor_responsavel_id' => ['nullable', 'integer', 'exists:professores,id_professor'],
            'situacao' => ['nullable', Rule::in(['ativa', 'inativa', 'concluida'])],
        ];
    }

    protected function formData(): array
    {
        return [
            'professores' => Professor::query()
                ->orderBy('nome')
                ->get(),
        ];
    }

    protected function applyIndexScope(Builder $query, Request $request): void
    {
        if ($request->user()?->tipo_usuario !== 'professor') {
            return;
        }

        $query->where(function (Builder $query) use ($request): void {
            $userId = $request->user()->getKey();

            $query->whereHas('alocacoes.professor', fn (Builder $query) => $query->where('user_id', $userId))
                ->orWhereHas('professorResponsavel', fn (Builder $query) => $query->where('user_id', $userId));
        });
    }
}

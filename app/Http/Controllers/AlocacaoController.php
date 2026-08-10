<?php

namespace App\Http\Controllers;

use App\Models\Disciplina;
use App\Models\DisciplinaProfessor;
use App\Models\Professor;
use App\Models\Turma;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;

class AlocacaoController extends CrudController
{
    protected string $modelClass = DisciplinaProfessor::class;

    protected string $primaryKey = 'id_disciplina_professor';

    protected array $searchable = ['id_disciplina_professor'];

    protected string $singularLabel = 'vínculo docente';

    protected string $pluralLabel = 'Alocações docentes';

    protected string $resource = 'alocacoes';

    protected array $with = ['professor', 'disciplina', 'turma'];

    protected array $relationSearches = [
        'professor' => ['nome', 'especialidade'],
        'disciplina' => ['nome', 'codigo'],
        'turma' => ['nome', 'serie', 'ano_letivo'],
    ];

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';
        $uniqueAssignment = $this->uniqueAssignmentRule($id);

        return [
            'professor_id' => [
                $prefix,
                'integer',
                Rule::exists((new Professor)->getTable(), 'id_professor')
                    ->where('situacao', 'ativo'),
            ],
            'disciplina_id' => [
                $prefix,
                'integer',
                Rule::exists((new Disciplina)->getTable(), 'id_disciplina')
                    ->where('situacao', 'ativa'),
            ],
            'turma_id' => [
                $prefix,
                'integer',
                Rule::exists((new Turma)->getTable(), 'id_turma')
                    ->where('situacao', 'ativa'),
                $uniqueAssignment,
            ],
        ];
    }

    protected function formData(): array
    {
        return [
            'professores' => Professor::query()
                ->select(['id_professor', 'nome', 'especialidade'])
                ->where('situacao', 'ativo')
                ->orderBy('nome')
                ->get(),
            'disciplinas' => Disciplina::query()
                ->select(['id_disciplina', 'nome', 'codigo'])
                ->where('situacao', 'ativa')
                ->orderBy('nome')
                ->get(),
            'turmas' => Turma::query()
                ->select(['id_turma', 'nome', 'serie', 'turno', 'ano_letivo'])
                ->where('situacao', 'ativa')
                ->orderByDesc('ano_letivo')
                ->orderBy('nome')
                ->get(),
        ];
    }

    protected function messages(): array
    {
        return [
            'professor_id.exists' => 'Selecione um professor ativo.',
            'disciplina_id.exists' => 'Selecione uma disciplina ativa.',
            'turma_id.exists' => 'Selecione uma turma ativa.',
            'turma_id.unique' => 'Este professor já está alocado nesta disciplina e turma.',
        ];
    }

    protected function attributes(): array
    {
        return [
            'professor_id' => 'professor',
            'disciplina_id' => 'disciplina',
            'turma_id' => 'turma',
        ];
    }

    /** @param array<string, mixed> $validated */
    protected function prepareCreate(array $validated): array
    {
        $this->validateAssignmentWithinTransaction($validated);

        return $validated;
    }

    /** @param array<string, mixed> $validated */
    protected function prepareUpdate(array $validated, Model $record): array
    {
        $final = array_replace($record->only([
            'professor_id',
            'disciplina_id',
            'turma_id',
        ]), $validated);

        $this->validateAssignmentWithinTransaction($final, (int) $record->getKey());

        return $validated;
    }

    /** @param array<string, mixed> $assignment */
    private function validateAssignmentWithinTransaction(array $assignment, ?int $ignoredId = null): void
    {
        Professor::query()
            ->whereKey((int) $assignment['professor_id'])
            ->lockForUpdate()
            ->firstOrFail();

        $allocations = DisciplinaProfessor::query()
            ->where('professor_id', $assignment['professor_id'])
            ->when(
                $ignoredId !== null,
                fn ($query) => $query->where('id_disciplina_professor', '!=', $ignoredId),
            );

        if ((clone $allocations)
            ->where('disciplina_id', $assignment['disciplina_id'])
            ->where('turma_id', $assignment['turma_id'])
            ->exists()) {
            throw ValidationException::withMessages([
                'turma_id' => 'Este professor já está alocado nesta disciplina e turma.',
            ]);
        }

        if ($allocations
            ->where('disciplina_id', '!=', $assignment['disciplina_id'])
            ->exists()) {
            throw ValidationException::withMessages([
                'disciplina_id' => 'Este professor já está alocado em outra disciplina. Um professor pode atuar em apenas uma disciplina, mesmo em turmas diferentes.',
            ]);
        }
    }

    private function uniqueAssignmentRule(?int $id): Unique
    {
        $record = $id === null ? null : DisciplinaProfessor::query()->find($id);
        $rule = Rule::unique((new DisciplinaProfessor)->getTable(), 'turma_id')
            ->where(fn ($query) => $query
                ->where('professor_id', request('professor_id', $record?->professor_id))
                ->where('disciplina_id', request('disciplina_id', $record?->disciplina_id)));

        if ($id !== null) {
            $rule->ignore($id, 'id_disciplina_professor');
        }

        return $rule;
    }
}

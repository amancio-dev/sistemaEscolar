<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotaResource;
use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\DisciplinaProfessor;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Professor;
use App\Models\Turma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NotaController extends CrudController
{
    protected string $modelClass = Nota::class;

    protected string $primaryKey = 'id_nota';

    protected array $searchable = ['avaliacao', 'periodo'];

    protected string $singularLabel = 'registro de nota';

    protected string $pluralLabel = 'Registros de notas';

    protected string $resource = 'notas';

    protected ?string $apiResourceClass = NotaResource::class;

    protected array $with = ['aluno', 'disciplina', 'turma', 'professor'];

    protected array $relationSearches = [
        'aluno' => ['nome', 'numero_matricula'],
        'disciplina' => ['nome', 'codigo'],
        'turma' => ['nome'],
        'professor' => ['nome'],
    ];

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';

        return [
            'aluno_id' => [$prefix, 'integer', 'exists:alunos,id_aluno'],
            'disciplina_id' => [$prefix, 'integer', 'exists:disciplinas,id_disciplina'],
            'turma_id' => [$prefix, 'integer', 'exists:turmas,id_turma'],
            'professor_id' => [$prefix, 'integer', 'exists:professores,id_professor'],
            'periodo' => [$prefix, Rule::in(['primeiro_bimestre', 'segundo_bimestre', 'terceiro_bimestre', 'quarto_bimestre'])],
            'avaliacao' => [$prefix, 'string', 'max:100'],
            'valor' => [$prefix, 'numeric', 'min:0', 'max:10'],
        ];
    }

    protected function formData(): array
    {
        if (request()->user()?->tipo_usuario === 'professor') {
            $professor = request()->user()->professor;
            $alocacoes = $professor?->alocacoes()->get() ?? collect();
            $turmaIds = $alocacoes->pluck('turma_id')->unique();

            return [
                'alunos' => Aluno::query()
                    ->whereHas('matriculas', fn (Builder $query) => $query
                        ->whereIn('turma_id', $turmaIds)
                        ->where('situacao', 'ativa'))
                    ->orderBy('nome')
                    ->get(),
                'disciplinas' => Disciplina::query()
                    ->whereIn('id_disciplina', $alocacoes->pluck('disciplina_id'))
                    ->orderBy('nome')
                    ->get(),
                'turmas' => Turma::query()
                    ->whereIn('id_turma', $turmaIds)
                    ->orderBy('nome')
                    ->get(),
                'professores' => $professor ? collect([$professor]) : collect(),
            ];
        }

        return [
            'alunos' => Aluno::query()->orderBy('nome')->get(),
            'disciplinas' => Disciplina::query()->orderBy('nome')->get(),
            'turmas' => Turma::query()->orderBy('nome')->get(),
            'professores' => Professor::query()->orderBy('nome')->get(),
        ];
    }

    /** @param array<string, mixed> $validated */
    protected function prepareCreate(array $validated): array
    {
        $this->validarVinculosAcademicos($validated);

        return $validated;
    }

    /** @param array<string, mixed> $validated */
    protected function prepareUpdate(array $validated, Model $record): array
    {
        $this->validarVinculosAcademicos(array_replace($record->only([
            'aluno_id',
            'disciplina_id',
            'turma_id',
            'professor_id',
        ]), $validated));

        return $validated;
    }

    protected function applyIndexScope(Builder $query, Request $request): void
    {
        if ($request->user()?->tipo_usuario === 'professor') {
            $query->whereHas('professor', fn (Builder $query) => $query
                ->where('user_id', $request->user()->getKey()));
        }
    }

    protected function authorizeRecord(Request $request, Model $record): void
    {
        if ($request->user()?->tipo_usuario === 'professor') {
            abort_unless(
                (int) $record->professor_id === (int) $request->user()->professor?->getKey(),
                403,
                'Você só pode gerenciar seus próprios registros de nota.',
            );
        }
    }

    /** @param array<string, mixed> $attributes */
    private function validarVinculosAcademicos(array $attributes): void
    {
        $errors = [];

        if (! Matricula::query()
            ->where('aluno_id', $attributes['aluno_id'])
            ->where('turma_id', $attributes['turma_id'])
            ->where('situacao', 'ativa')
            ->exists()) {
            $errors['aluno_id'][] = 'O aluno precisa ter matrícula ativa na turma selecionada.';
        }

        if (! DisciplinaProfessor::query()
            ->where('professor_id', $attributes['professor_id'])
            ->where('disciplina_id', $attributes['disciplina_id'])
            ->where('turma_id', $attributes['turma_id'])
            ->lockForUpdate()
            ->first()) {
            $errors['professor_id'][] = 'O professor não está alocado nesta disciplina e turma.';
        }

        $user = request()->user();
        if ($user?->tipo_usuario === 'professor'
            && (int) $user->professor?->getKey() !== (int) $attributes['professor_id']) {
            $errors['professor_id'][] = 'Você só pode registrar notas em seu próprio nome.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Frequencia;
use App\Models\Professor;
use App\Models\Turma;
use Illuminate\Validation\Rule;

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

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';

        return [
            'aluno_id' => [$prefix, 'integer', 'exists:alunos,id_aluno'],
            'disciplina_id' => [$prefix, 'integer', 'exists:disciplinas,id_disciplina'],
            'turma_id' => [$prefix, 'integer', 'exists:turmas,id_turma'],
            'professor_id' => [$prefix, 'integer', 'exists:professores,id_professor'],
            'data_aula' => [$prefix, 'date'],
            'situacao' => [$prefix, Rule::in(['presente', 'ausente', 'justificada', 'atrasado'])],
            'justificativa' => ['nullable', 'string'],
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
}

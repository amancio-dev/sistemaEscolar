<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Nota;
use App\Models\Professor;
use App\Models\Turma;
use Illuminate\Validation\Rule;

class NotaController extends CrudController
{
    protected string $modelClass = Nota::class;

    protected string $primaryKey = 'id_nota';

    protected array $searchable = ['avaliacao', 'periodo'];

    protected string $singularLabel = 'registro de nota';

    protected string $pluralLabel = 'Registros de notas';

    protected string $resource = 'notas';

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
        return [
            'alunos' => Aluno::query()->orderBy('nome')->get(),
            'disciplinas' => Disciplina::query()->orderBy('nome')->get(),
            'turmas' => Turma::query()->orderBy('nome')->get(),
            'professores' => Professor::query()->orderBy('nome')->get(),
        ];
    }
}

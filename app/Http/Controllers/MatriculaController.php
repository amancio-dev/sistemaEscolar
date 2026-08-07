<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Turma;
use Illuminate\Validation\Rule;

class MatriculaController extends CrudController
{
    protected string $modelClass = Matricula::class;

    protected string $primaryKey = 'id_matricula';

    protected array $searchable = ['id_matricula', 'ano_letivo', 'situacao'];

    protected string $singularLabel = 'registro de matrícula';

    protected string $pluralLabel = 'Registros de matrículas';

    protected string $resource = 'matriculas';

    protected array $with = ['aluno', 'turma'];

    protected array $relationSearches = [
        'aluno' => ['nome', 'numero_matricula'],
        'turma' => ['nome', 'serie'],
    ];

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';

        return [
            'aluno_id' => [$prefix, 'integer', 'exists:alunos,id_aluno'],
            'turma_id' => [$prefix, 'integer', 'exists:turmas,id_turma'],
            'data_matricula' => [$prefix, 'date'],
            'ano_letivo' => [$prefix, 'digits:4'],
            'situacao' => [$prefix, Rule::in(['ativa', 'trancada', 'cancelada', 'transferida', 'concluida'])],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    protected function formData(): array
    {
        return [
            'alunos' => Aluno::query()->orderBy('nome')->get(),
            'turmas' => Turma::query()->orderBy('nome')->get(),
        ];
    }
}

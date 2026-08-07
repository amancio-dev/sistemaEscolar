<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Models\Professor;
use Illuminate\Validation\Rule;

class TurmaController extends CrudController
{
    protected string $modelClass = Turma::class;

    protected string $primaryKey = 'id_turma';

    protected array $searchable = ['nome', 'serie', 'turno', 'sala', 'ano_letivo', 'situacao'];

    protected string $singularLabel = 'registro de turma';

    protected string $pluralLabel = 'Registros de turmas';

    protected string $resource = 'turmas';

    protected array $with = ['professorResponsavel'];

    protected array $relationSearches = [
        'professorResponsavel' => ['nome'],
    ];

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
}

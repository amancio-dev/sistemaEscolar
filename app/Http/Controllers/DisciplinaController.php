<?php

namespace App\Http\Controllers;

use App\Models\Disciplina;
use Illuminate\Validation\Rule;

class DisciplinaController extends CrudController
{
    protected string $modelClass = Disciplina::class;

    protected string $primaryKey = 'id_disciplina';

    protected array $searchable = ['nome', 'codigo', 'descricao', 'situacao'];

    protected string $singularLabel = 'registro de disciplina';

    protected string $pluralLabel = 'Registros de disciplinas';

    protected string $resource = 'disciplinas';

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';

        return [
            'nome' => [$prefix, 'string', 'max:255'],
            'codigo' => [$prefix, 'string', 'max:50', Rule::unique('disciplinas', 'codigo')->ignore($id, 'id_disciplina')],
            'descricao' => ['nullable', 'string'],
            'carga_horaria' => [$prefix, 'integer', 'min:1', 'max:2000'],
            'media_minima' => [$prefix, 'numeric', 'min:0', 'max:10'],
            'situacao' => ['nullable', Rule::in(['ativa', 'inativa'])],
        ];
    }
}

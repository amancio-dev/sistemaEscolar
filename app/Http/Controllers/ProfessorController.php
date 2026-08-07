<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfessorController extends CrudController
{
    protected string $modelClass = Professor::class;

    protected string $primaryKey = 'id_professor';

    protected array $searchable = ['nome', 'cpf', 'email', 'formacao', 'especialidade', 'situacao'];

    protected string $singularLabel = 'registro de professor';

    protected string $pluralLabel = 'Registros de professores';

    protected string $resource = 'professores';

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';

        return [
            'nome' => [$prefix, 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id', Rule::unique('professores', 'user_id')->ignore($id, 'id_professor')],
            'cpf' => [$prefix, 'string', 'max:14', Rule::unique('professores', 'cpf')->ignore($id, 'id_professor')],
            'telefone' => ['nullable', 'string', 'max:20'],
            'email' => [$prefix, 'email', 'max:255', Rule::unique('professores', 'email')->ignore($id, 'id_professor')],
            'endereco' => ['nullable', 'string'],
            'formacao' => ['nullable', 'string', 'max:255'],
            'especialidade' => ['nullable', 'string', 'max:255'],
            'data_contratacao' => ['nullable', 'date'],
            'situacao' => ['nullable', Rule::in(['ativo', 'inativo', 'afastado'])],
        ];
    }

    protected function prepareCreate(array $validated): array
    {
        if (empty($validated['user_id'])) {
            $account = preg_replace('/\D/', '', $validated['cpf']) ?: Str::random(12);
            $user = User::create([
                'name' => $validated['nome'],
                'email' => "professor.{$account}@sistema.local",
                'password' => Str::random(40),
                'tipo_usuario' => 'professor',
                'situacao' => 'ativo',
            ]);
            $validated['user_id'] = $user->id;
        }

        return $validated;
    }

    protected function afterUpdate(Model $record): void
    {
        User::query()->whereKey($record->user_id)->update(['name' => $record->nome]);
    }

    protected function afterDelete(Model $record): void
    {
        User::query()->whereKey($record->user_id)->delete();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AlunoController extends CrudController
{
    protected string $modelClass = Aluno::class;

    protected string $primaryKey = 'id_aluno';

    protected array $searchable = ['nome', 'numero_matricula', 'cpf', 'email', 'situacao'];

    protected string $singularLabel = 'registro de aluno';

    protected string $pluralLabel = 'Registros de alunos';

    protected string $resource = 'alunos';

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';

        return [
            'nome' => [$prefix, 'string', 'max:255'],
            'numero_matricula' => [$prefix, 'string', 'max:255', Rule::unique('alunos', 'numero_matricula')->ignore($id, 'id_aluno')],
            'user_id' => ['nullable', 'integer', 'exists:users,id', Rule::unique('alunos', 'user_id')->ignore($id, 'id_aluno')],
            'cpf' => [$prefix, 'string', 'max:14', Rule::unique('alunos', 'cpf')->ignore($id, 'id_aluno')],
            'data_nascimento' => [$prefix, 'date', 'before:today'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('alunos', 'email')->ignore($id, 'id_aluno')],
            'endereco' => ['nullable', 'string'],
            'nome_responsavel' => ['nullable', 'string', 'max:255'],
            'telefone_responsavel' => ['nullable', 'string', 'max:20'],
            'situacao' => ['nullable', Rule::in(['ativo', 'inativo', 'transferido', 'concluido'])],
        ];
    }

    protected function prepareCreate(array $validated): array
    {
        if (empty($validated['user_id'])) {
            $account = Str::lower(Str::slug($validated['numero_matricula'], '.'));
            $user = User::create([
                'name' => $validated['nome'],
                'email' => "aluno.{$account}@sistema.local",
                'password' => Str::random(40),
                'tipo_usuario' => 'aluno',
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

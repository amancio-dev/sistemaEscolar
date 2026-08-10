<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
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
        $cpfDigits = preg_replace('/\D/', '', (string) request('cpf'));

        if (strlen($cpfDigits) === 11) {
            request()->merge([
                'cpf' => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfDigits),
            ]);
        }

        $linkedUserId = request()->integer('user_id') ?: ($id
            ? Professor::query()->whereKey($id)->value('user_id')
            : null);

        $prefix = $id === null ? 'required' : 'sometimes';

        return [
            'nome' => [$prefix, 'string', 'max:255'],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('tipo_usuario', 'professor'),
                Rule::unique('professores', 'user_id')->ignore($id, 'id_professor'),
            ],
            'cpf' => [
                $prefix,
                'string',
                'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                Rule::unique('professores', 'cpf')->ignore($id, 'id_professor'),
                Rule::unique('users', 'cpf')->ignore($linkedUserId),
            ],
            'telefone' => ['nullable', 'string', 'max:20'],
            'email' => [
                $prefix,
                'email',
                'max:255',
                Rule::unique('professores', 'email')->ignore($id, 'id_professor'),
                Rule::unique('users', 'email')->ignore($linkedUserId),
            ],
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
            $cpfDigits = preg_replace('/\D/', '', $validated['cpf']);
            $user = User::create([
                'name' => $validated['nome'],
                'email' => $validated['email'],
                'cpf' => $validated['cpf'],
                'password' => Hash::make($cpfDigits),
                'tipo_usuario' => 'professor',
                'situacao' => ($validated['situacao'] ?? 'ativo') === 'ativo' ? 'ativo' : 'inativo',
            ]);
            $validated['user_id'] = $user->id;
        } else {
            $user = User::query()->findOrFail($validated['user_id']);

            $user->update([
                'name' => $validated['nome'],
                'email' => $validated['email'],
                'cpf' => $validated['cpf'],
                'password' => preg_replace('/\D/', '', $validated['cpf']),
                'situacao' => ($validated['situacao'] ?? 'ativo') === 'ativo' ? 'ativo' : 'inativo',
            ]);
        }

        return $validated;
    }

    protected function messages(): array
    {
        return [
            'cpf.regex' => 'Informe um CPF válido, no formato 000.000.000-00.',
        ];
    }

    protected function afterUpdate(Model $record): void
    {
        User::query()->whereKey($record->user_id)->update([
            'name' => $record->nome,
            'email' => $record->email,
            'cpf' => $record->cpf,
            'password' => Hash::make((string) preg_replace('/\D/', '', $record->cpf)),
            'situacao' => $record->situacao === 'ativo' ? 'ativo' : 'inativo',
        ]);
    }

    protected function afterDelete(Model $record): void
    {
        User::query()->whereKey($record->user_id)->delete();
    }
}

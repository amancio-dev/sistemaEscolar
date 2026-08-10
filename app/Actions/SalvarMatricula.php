<?php

namespace App\Actions;

use App\Models\Matricula;
use App\Models\Turma;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalvarMatricula
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes, ?int $matriculaId = null): Matricula
    {
        return DB::transaction(function () use ($attributes, $matriculaId): Matricula {
            $matricula = $matriculaId === null
                ? null
                : Matricula::query()->lockForUpdate()->findOrFail($matriculaId);

            $finalAttributes = $matricula === null
                ? $attributes
                : array_replace($matricula->only([
                    'aluno_id',
                    'turma_id',
                    'data_matricula',
                    'ano_letivo',
                    'situacao',
                    'observacoes',
                ]), $attributes);

            $turma = Turma::query()
                ->lockForUpdate()
                ->findOrFail((int) $finalAttributes['turma_id']);

            $this->validateAcademicRules($finalAttributes, $turma, $matricula);

            if ($matricula === null) {
                return Matricula::create($attributes);
            }

            $matricula->update($attributes);

            return $matricula->refresh();
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function validateAcademicRules(array $attributes, Turma $turma, ?Matricula $matricula): void
    {
        $errors = [];

        if ((int) $attributes['ano_letivo'] !== (int) $turma->ano_letivo) {
            $errors['ano_letivo'][] = "O ano letivo da matrícula deve ser o mesmo da turma selecionada ({$turma->ano_letivo}).";
        }

        $duplicateQuery = Matricula::query()
            ->where('aluno_id', $attributes['aluno_id'])
            ->where('turma_id', $turma->getKey())
            ->where('ano_letivo', $attributes['ano_letivo']);

        if ($matricula !== null) {
            $duplicateQuery->where($matricula->getKeyName(), '!=', $matricula->getKey());
        }

        if ($duplicateQuery->exists()) {
            $errors['aluno_id'][] = 'Este aluno já possui matrícula nesta turma para o ano letivo informado.';
        }

        if ($attributes['situacao'] === 'ativa') {
            if ($turma->situacao !== 'ativa') {
                $errors['turma_id'][] = 'Não é possível manter uma matrícula ativa em uma turma inativa ou concluída.';
            }

            $activeEnrollmentsQuery = Matricula::query()
                ->where('turma_id', $turma->getKey())
                ->where('situacao', 'ativa');

            if ($matricula !== null) {
                $activeEnrollmentsQuery->where($matricula->getKeyName(), '!=', $matricula->getKey());
            }

            $activeEnrollments = $activeEnrollmentsQuery->count();

            if ($activeEnrollments >= (int) $turma->limite_alunos) {
                $errors['turma_id'][] = "A turma selecionada está lotada ({$activeEnrollments} de {$turma->limite_alunos} vagas ocupadas).";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}

<?php

namespace App\Actions;

use App\Models\Disciplina;
use App\Models\DisciplinaProfessor;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Professor;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarChamadaEmLote
{
    /**
     * @param  array<int, array{aluno_id: int, situacao: string, justificativa?: string|null}>  $frequencias
     */
    public function execute(
        DisciplinaProfessor $alocacao,
        string $dataAula,
        array $frequencias,
        User $actor,
    ): int {
        return DB::transaction(function () use ($alocacao, $dataAula, $frequencias, $actor): int {
            $alocacao = DisciplinaProfessor::query()
                ->lockForUpdate()
                ->findOrFail($alocacao->getKey());
            $professor = Professor::query()->lockForUpdate()->findOrFail($alocacao->professor_id);
            $disciplina = Disciplina::query()->lockForUpdate()->findOrFail($alocacao->disciplina_id);
            $turma = Turma::query()->lockForUpdate()->findOrFail($alocacao->turma_id);

            if ($actor->tipo_usuario === 'professor'
                && (int) $professor->user_id !== (int) $actor->getKey()) {
                throw new AuthorizationException('Você só pode registrar chamadas nas suas próprias alocações.');
            }

            if ($professor->situacao !== 'ativo'
                || $disciplina->situacao !== 'ativa'
                || $turma->situacao !== 'ativa') {
                throw ValidationException::withMessages([
                    'alocacao_id' => 'A chamada exige professor, disciplina e turma ativos.',
                ]);
            }

            $alunosEnviados = collect($frequencias)
                ->pluck('aluno_id')
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values();

            $alunosMatriculados = Matricula::query()
                ->where('turma_id', $alocacao->turma_id)
                ->where('situacao', 'ativa')
                ->whereHas('aluno', fn ($query) => $query->where('situacao', 'ativo'))
                ->lockForUpdate()
                ->pluck('aluno_id')
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values();

            if ($alunosEnviados->all() !== $alunosMatriculados->all()) {
                throw ValidationException::withMessages([
                    'frequencias' => 'As matrículas da turma mudaram. Recarregue a chamada antes de salvar.',
                ]);
            }

            $dataNormalizada = Carbon::createFromFormat('Y-m-d', $dataAula)
                ->startOfDay()
                ->toDateString();

            foreach ($frequencias as $frequencia) {
                Frequencia::query()->updateOrCreate([
                    'aluno_id' => (int) $frequencia['aluno_id'],
                    'disciplina_id' => (int) $alocacao->disciplina_id,
                    'turma_id' => (int) $alocacao->turma_id,
                    'data_aula' => $dataNormalizada,
                ], [
                    'professor_id' => (int) $alocacao->professor_id,
                    'situacao' => $frequencia['situacao'],
                    'justificativa' => filled($frequencia['justificativa'] ?? null)
                        ? trim((string) $frequencia['justificativa'])
                        : null,
                ]);
            }

            return count($frequencias);
        }, 3);
    }
}

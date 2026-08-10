<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const QUARANTINE_TABLE = 'disciplina_professor_conflitos_quarentena';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable(self::QUARANTINE_TABLE)) {
            throw new RuntimeException('A tabela de quarentena de alocações docentes não existe.');
        }

        DB::transaction(function (): void {
            DB::table('professores')
                ->select(['id_professor', 'especialidade'])
                ->whereExists(fn ($query) => $query
                    ->selectRaw('1')
                    ->from('disciplina_professor')
                    ->whereColumn('disciplina_professor.professor_id', 'professores.id_professor'))
                ->orderBy('id_professor')
                ->chunkById(100, function ($professores): void {
                    foreach ($professores as $professor) {
                        $this->quarantineConflictsFor($professor);
                    }
                }, 'id_professor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable(self::QUARANTINE_TABLE)) {
            return;
        }

        DB::transaction(function (): void {
            DB::table('disciplina_professor')->insertUsing(
                $this->allocationColumns(),
                DB::table(self::QUARANTINE_TABLE)->select($this->allocationColumns()),
            );

            DB::table(self::QUARANTINE_TABLE)->delete();
        });
    }

    private function quarantineConflictsFor(object $professor): void
    {
        $allocations = DB::table('disciplina_professor')
            ->join(
                'disciplinas',
                'disciplinas.id_disciplina',
                '=',
                'disciplina_professor.disciplina_id',
            )
            ->where('disciplina_professor.professor_id', $professor->id_professor)
            ->orderBy('disciplina_professor.disciplina_id')
            ->orderBy('disciplina_professor.id_disciplina_professor')
            ->get([
                'disciplina_professor.id_disciplina_professor',
                'disciplina_professor.professor_id',
                'disciplina_professor.disciplina_id',
                'disciplina_professor.turma_id',
                'disciplina_professor.created_at',
                'disciplina_professor.updated_at',
                'disciplinas.nome as disciplina_nome',
            ]);

        $disciplines = $allocations
            ->unique('disciplina_id')
            ->sortBy('disciplina_id')
            ->values();

        if ($disciplines->count() <= 1) {
            return;
        }

        $normalizedSpecialty = $this->normalize((string) $professor->especialidade);
        $matchingDiscipline = $normalizedSpecialty === ''
            ? null
            : $disciplines->first(fn (object $allocation): bool => $this->normalize(
                (string) $allocation->disciplina_nome,
            ) === $normalizedSpecialty);

        $canonicalDisciplineId = (int) ($matchingDiscipline?->disciplina_id
            ?? $disciplines->first()->disciplina_id);
        $criterion = $matchingDiscipline === null
            ? 'menor_disciplina_id'
            : 'especialidade';
        $quarantinedAt = now();
        $conflicts = $allocations
            ->filter(fn (object $allocation): bool => (int) $allocation->disciplina_id !== $canonicalDisciplineId)
            ->values();

        DB::table(self::QUARANTINE_TABLE)->insert(
            $conflicts->map(fn (object $allocation): array => [
                'id_disciplina_professor' => $allocation->id_disciplina_professor,
                'professor_id' => $allocation->professor_id,
                'disciplina_id' => $allocation->disciplina_id,
                'turma_id' => $allocation->turma_id,
                'created_at' => $allocation->created_at,
                'updated_at' => $allocation->updated_at,
                'disciplina_canonica_id' => $canonicalDisciplineId,
                'criterio_canonico' => $criterion,
                'especialidade_professor' => $professor->especialidade,
                'motivo_quarentena' => 'professor_com_multiplas_disciplinas',
                'quarentena_em' => $quarantinedAt,
            ])->all(),
        );

        DB::table('disciplina_professor')
            ->whereIn(
                'id_disciplina_professor',
                $conflicts->pluck('id_disciplina_professor'),
            )
            ->delete();
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::ascii(trim($value)));
    }

    /** @return array<int, string> */
    private function allocationColumns(): array
    {
        return [
            'id_disciplina_professor',
            'professor_id',
            'disciplina_id',
            'turma_id',
            'created_at',
            'updated_at',
        ];
    }
};

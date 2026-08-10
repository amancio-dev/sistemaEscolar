<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'frequencias_aluno_disciplina_turma_data_unique';

    private const QUARANTINE_TABLE = 'frequencias_duplicadas_quarentena';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createQuarantineTable();
        $this->quarantineLegacyDuplicates();

        if (! Schema::hasIndex('frequencias', self::INDEX_NAME)) {
            Schema::table('frequencias', function (Blueprint $table) {
                $table->unique(
                    ['aluno_id', 'disciplina_id', 'turma_id', 'data_aula'],
                    self::INDEX_NAME,
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('frequencias', self::INDEX_NAME)) {
            Schema::table('frequencias', function (Blueprint $table) {
                $table->dropUnique(self::INDEX_NAME);
            });
        }

        if (! Schema::hasTable(self::QUARANTINE_TABLE)) {
            return;
        }

        DB::transaction(function (): void {
            DB::table('frequencias')->insertUsing(
                $this->attendanceColumns(),
                DB::table(self::QUARANTINE_TABLE)->select($this->attendanceColumns()),
            );
        });

        Schema::drop(self::QUARANTINE_TABLE);
    }

    private function createQuarantineTable(): void
    {
        if (Schema::hasTable(self::QUARANTINE_TABLE)) {
            return;
        }

        Schema::create(self::QUARANTINE_TABLE, function (Blueprint $table) {
            $table->unsignedBigInteger('id_frequencia')->primary();
            $table->unsignedBigInteger('aluno_id');
            $table->unsignedBigInteger('disciplina_id');
            $table->unsignedBigInteger('turma_id');
            $table->unsignedBigInteger('professor_id');
            $table->date('data_aula');
            $table->string('situacao');
            $table->text('justificativa')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('motivo_quarentena');
            $table->timestamp('quarentena_em');
        });
    }

    private function quarantineLegacyDuplicates(): void
    {
        DB::transaction(function (): void {
            $quarantinedAt = now();
            $duplicates = DB::table('frequencias as candidate')
                ->select(array_map(
                    fn (string $column): string => "candidate.{$column}",
                    $this->attendanceColumns(),
                ))
                ->selectRaw('? as motivo_quarentena', ['duplicidade_legada'])
                ->selectRaw('? as quarentena_em', [$quarantinedAt])
                ->whereExists(function ($query): void {
                    // A maior chave primária do grupo permanece como canônica.
                    $query->selectRaw('1')
                        ->from('frequencias as canonical')
                        ->whereColumn('canonical.aluno_id', 'candidate.aluno_id')
                        ->whereColumn('canonical.disciplina_id', 'candidate.disciplina_id')
                        ->whereColumn('canonical.turma_id', 'candidate.turma_id')
                        ->whereColumn('canonical.data_aula', 'candidate.data_aula')
                        ->whereColumn('canonical.id_frequencia', '>', 'candidate.id_frequencia');
                });

            DB::table(self::QUARANTINE_TABLE)->insertUsing([
                ...$this->attendanceColumns(),
                'motivo_quarentena',
                'quarentena_em',
            ], $duplicates);

            DB::table(self::QUARANTINE_TABLE)
                ->select('id_frequencia')
                ->chunkById(500, function ($records): void {
                    DB::table('frequencias')
                        ->whereIn('id_frequencia', $records->pluck('id_frequencia'))
                        ->delete();
                }, 'id_frequencia');
        });
    }

    /** @return array<int, string> */
    private function attendanceColumns(): array
    {
        return [
            'id_frequencia',
            'aluno_id',
            'disciplina_id',
            'turma_id',
            'professor_id',
            'data_aula',
            'situacao',
            'justificativa',
            'created_at',
            'updated_at',
        ];
    }
};

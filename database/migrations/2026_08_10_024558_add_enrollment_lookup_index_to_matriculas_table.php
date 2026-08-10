<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'matriculas_aluno_turma_ano_unique';

    private const QUARANTINE_TABLE = 'matriculas_duplicadas_quarentena';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createQuarantineTable();
        $this->quarantineLegacyDuplicates();

        if (! Schema::hasIndex('matriculas', self::INDEX_NAME)) {
            Schema::table('matriculas', function (Blueprint $table) {
                $table->unique(
                    ['aluno_id', 'turma_id', 'ano_letivo'],
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
        if (Schema::hasIndex('matriculas', self::INDEX_NAME)) {
            Schema::table('matriculas', function (Blueprint $table) {
                $table->dropUnique(self::INDEX_NAME);
            });
        }

        if (! Schema::hasTable(self::QUARANTINE_TABLE)) {
            return;
        }

        DB::transaction(function (): void {
            DB::table('matriculas')->insertUsing(
                $this->enrollmentColumns(),
                DB::table(self::QUARANTINE_TABLE)->select($this->enrollmentColumns()),
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
            $table->unsignedBigInteger('id_matricula')->primary();
            $table->unsignedBigInteger('aluno_id');
            $table->unsignedBigInteger('turma_id');
            $table->date('data_matricula');
            $table->year('ano_letivo');
            $table->string('situacao');
            $table->text('observacoes')->nullable();
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
            $duplicates = DB::table('matriculas as candidate')
                ->select(array_map(
                    fn (string $column): string => "candidate.{$column}",
                    $this->enrollmentColumns(),
                ))
                ->selectRaw('? as motivo_quarentena', ['duplicidade_legada'])
                ->selectRaw('? as quarentena_em', [$quarantinedAt])
                ->whereExists(function ($query): void {
                    // A maior chave primária do grupo permanece como canônica.
                    $query->selectRaw('1')
                        ->from('matriculas as canonical')
                        ->whereColumn('canonical.aluno_id', 'candidate.aluno_id')
                        ->whereColumn('canonical.turma_id', 'candidate.turma_id')
                        ->whereColumn('canonical.ano_letivo', 'candidate.ano_letivo')
                        ->whereColumn('canonical.id_matricula', '>', 'candidate.id_matricula');
                });

            DB::table(self::QUARANTINE_TABLE)->insertUsing([
                ...$this->enrollmentColumns(),
                'motivo_quarentena',
                'quarentena_em',
            ], $duplicates);

            DB::table(self::QUARANTINE_TABLE)
                ->select('id_matricula')
                ->chunkById(500, function ($records): void {
                    DB::table('matriculas')
                        ->whereIn('id_matricula', $records->pluck('id_matricula'))
                        ->delete();
                }, 'id_matricula');
        });
    }

    /** @return array<int, string> */
    private function enrollmentColumns(): array
    {
        return [
            'id_matricula',
            'aluno_id',
            'turma_id',
            'data_matricula',
            'ano_letivo',
            'situacao',
            'observacoes',
            'created_at',
            'updated_at',
        ];
    }
};

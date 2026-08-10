<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Professor;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationUpgradeSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_duplicates_are_quarantined_uniquely_and_restored_on_rollback(): void
    {
        $attendanceMigration = require database_path(
            'migrations/2026_08_10_023123_add_attendance_lookup_index_to_frequencias_table.php'
        );
        $enrollmentMigration = require database_path(
            'migrations/2026_08_10_024558_add_enrollment_lookup_index_to_matriculas_table.php'
        );
        $enrollmentMigration->down();
        $attendanceMigration->down();

        $studentUser = User::factory()->create(['tipo_usuario' => 'aluno']);
        $teacherUser = User::factory()->create(['tipo_usuario' => 'professor']);
        $student = Aluno::create([
            'user_id' => $studentUser->getKey(),
            'numero_matricula' => 'LEGACY001',
            'nome' => 'Aluno legado',
            'cpf' => '111.222.333-44',
            'data_nascimento' => '2010-01-01',
            'situacao' => 'ativo',
        ]);
        $teacher = Professor::create([
            'user_id' => $teacherUser->getKey(),
            'nome' => 'Professor legado',
            'cpf' => '555.666.777-88',
            'email' => 'professor.legado@escola.test',
            'situacao' => 'ativo',
        ]);
        $class = Turma::create([
            'nome' => 'Turma legada',
            'serie' => 'Ensino Médio',
            'turno' => 'matutino',
            'sala' => '101',
            'ano_letivo' => 2026,
            'limite_alunos' => 30,
            'situacao' => 'ativa',
        ]);
        $subject = Disciplina::create([
            'nome' => 'Matemática',
            'codigo' => 'MAT-LEGACY',
            'carga_horaria' => 80,
            'media_minima' => 6,
            'situacao' => 'ativa',
        ]);

        $firstEnrollment = Matricula::create([
            'aluno_id' => $student->getKey(),
            'turma_id' => $class->getKey(),
            'data_matricula' => '2026-02-01',
            'ano_letivo' => 2026,
            'situacao' => 'trancada',
            'observacoes' => 'Registro original preservado',
        ]);
        $canonicalEnrollment = Matricula::create([
            'aluno_id' => $student->getKey(),
            'turma_id' => $class->getKey(),
            'data_matricula' => '2026-02-02',
            'ano_letivo' => 2026,
            'situacao' => 'ativa',
            'observacoes' => 'Registro canônico',
        ]);

        $firstAttendance = Frequencia::create([
            'aluno_id' => $student->getKey(),
            'disciplina_id' => $subject->getKey(),
            'turma_id' => $class->getKey(),
            'professor_id' => $teacher->getKey(),
            'data_aula' => '2026-08-01',
            'situacao' => 'ausente',
            'justificativa' => 'Registro original preservado',
        ]);
        $canonicalAttendance = Frequencia::create([
            'aluno_id' => $student->getKey(),
            'disciplina_id' => $subject->getKey(),
            'turma_id' => $class->getKey(),
            'professor_id' => $teacher->getKey(),
            'data_aula' => '2026-08-01',
            'situacao' => 'presente',
        ]);

        $attendanceMigration->up();
        $enrollmentMigration->up();

        $this->assertSame(
            $canonicalAttendance->getKey(),
            Frequencia::query()->sole()->getKey(),
        );
        $this->assertSame(
            $canonicalEnrollment->getKey(),
            Matricula::query()->sole()->getKey(),
        );
        $this->assertDatabaseHas('frequencias_duplicadas_quarentena', [
            'id_frequencia' => $firstAttendance->getKey(),
            'situacao' => 'ausente',
            'justificativa' => 'Registro original preservado',
            'motivo_quarentena' => 'duplicidade_legada',
        ]);
        $this->assertDatabaseHas('matriculas_duplicadas_quarentena', [
            'id_matricula' => $firstEnrollment->getKey(),
            'situacao' => 'trancada',
            'observacoes' => 'Registro original preservado',
            'motivo_quarentena' => 'duplicidade_legada',
        ]);
        $this->assertTrue($this->indexIsUnique(
            'frequencias',
            'frequencias_aluno_disciplina_turma_data_unique',
        ));
        $this->assertTrue($this->indexIsUnique(
            'matriculas',
            'matriculas_aluno_turma_ano_unique',
        ));

        $enrollmentMigration->down();
        $attendanceMigration->down();

        $this->assertFalse(Schema::hasTable('frequencias_duplicadas_quarentena'));
        $this->assertFalse(Schema::hasTable('matriculas_duplicadas_quarentena'));
        $this->assertEqualsCanonicalizing(
            [$firstAttendance->getKey(), $canonicalAttendance->getKey()],
            Frequencia::query()->pluck('id_frequencia')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$firstEnrollment->getKey(), $canonicalEnrollment->getKey()],
            Matricula::query()->pluck('id_matricula')->all(),
        );

        // Mantém o schema do processo de testes no estado final das migrations.
        $attendanceMigration->up();
        $enrollmentMigration->up();
    }

    private function indexIsUnique(string $table, string $indexName): bool
    {
        $index = collect(Schema::getIndexes($table))
            ->firstWhere('name', $indexName);

        return (bool) ($index['unique'] ?? false);
    }
}

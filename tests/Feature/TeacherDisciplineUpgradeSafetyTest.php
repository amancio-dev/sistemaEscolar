<?php

namespace Tests\Feature;

use App\Models\Disciplina;
use App\Models\DisciplinaProfessor;
use App\Models\Professor;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeacherDisciplineUpgradeSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_conflicting_legacy_disciplines_are_quarantined_by_specialty_and_restored(): void
    {
        $migration = require database_path(
            'migrations/2026_08_10_040001_quarantine_conflicting_teacher_discipline_allocations.php'
        );

        $mathematics = $this->createDiscipline('Matemática', 'MAT-UPGRADE');
        $portuguese = $this->createDiscipline('Português', 'POR-UPGRADE');
        $geography = $this->createDiscipline('Geografia', 'GEO-UPGRADE');
        $biology = $this->createDiscipline('Biologia', 'BIO-UPGRADE');
        $firstClass = $this->createClass('Turma Upgrade A', '401');
        $secondClass = $this->createClass('Turma Upgrade B', '402');

        $specialist = $this->createProfessor('Especialista em Português', 'PORTUGUES');
        $wrongSpecialtyAllocation = $this->createAllocation($specialist, $mathematics, $firstClass);
        $portugueseFirstClass = $this->createAllocation($specialist, $portuguese, $firstClass);
        $portugueseSecondClass = $this->createAllocation($specialist, $portuguese, $secondClass);

        $fallbackProfessor = $this->createProfessor('Professor sem correspondência', 'Educação Geral');
        $fallbackCanonical = $this->createAllocation($fallbackProfessor, $geography, $firstClass);
        $fallbackConflict = $this->createAllocation($fallbackProfessor, $biology, $secondClass);

        $migration->up();

        $this->assertEqualsCanonicalizing(
            [$portugueseFirstClass->getKey(), $portugueseSecondClass->getKey()],
            DisciplinaProfessor::query()
                ->where('professor_id', $specialist->getKey())
                ->pluck('id_disciplina_professor')
                ->all(),
        );
        $this->assertSame(
            [$fallbackCanonical->getKey()],
            DisciplinaProfessor::query()
                ->where('professor_id', $fallbackProfessor->getKey())
                ->pluck('id_disciplina_professor')
                ->all(),
        );
        $this->assertDatabaseHas('disciplina_professor_conflitos_quarentena', [
            'id_disciplina_professor' => $wrongSpecialtyAllocation->getKey(),
            'professor_id' => $specialist->getKey(),
            'disciplina_id' => $mathematics->getKey(),
            'turma_id' => $firstClass->getKey(),
            'disciplina_canonica_id' => $portuguese->getKey(),
            'criterio_canonico' => 'especialidade',
            'motivo_quarentena' => 'professor_com_multiplas_disciplinas',
        ]);
        $this->assertDatabaseHas('disciplina_professor_conflitos_quarentena', [
            'id_disciplina_professor' => $fallbackConflict->getKey(),
            'disciplina_canonica_id' => $geography->getKey(),
            'criterio_canonico' => 'menor_disciplina_id',
        ]);
        $this->assertSame(2, DB::table('disciplina_professor_conflitos_quarentena')->count());

        $migration->down();

        $this->assertTrue(Schema::hasTable('disciplina_professor_conflitos_quarentena'));
        $this->assertSame(0, DB::table('disciplina_professor_conflitos_quarentena')->count());
        $this->assertEqualsCanonicalizing(
            [
                $wrongSpecialtyAllocation->getKey(),
                $portugueseFirstClass->getKey(),
                $portugueseSecondClass->getKey(),
            ],
            DisciplinaProfessor::query()
                ->where('professor_id', $specialist->getKey())
                ->pluck('id_disciplina_professor')
                ->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$fallbackCanonical->getKey(), $fallbackConflict->getKey()],
            DisciplinaProfessor::query()
                ->where('professor_id', $fallbackProfessor->getKey())
                ->pluck('id_disciplina_professor')
                ->all(),
        );

        // Mantém o estado lógico da migration durante o restante do processo de testes.
        $migration->up();
    }

    private function createProfessor(string $name, string $specialty): Professor
    {
        $user = User::factory()->create([
            'tipo_usuario' => 'professor',
            'situacao' => 'ativo',
        ]);

        return Professor::create([
            'user_id' => $user->getKey(),
            'nome' => $name,
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'email' => fake()->unique()->safeEmail(),
            'especialidade' => $specialty,
            'situacao' => 'ativo',
        ]);
    }

    private function createDiscipline(string $name, string $code): Disciplina
    {
        return Disciplina::create([
            'nome' => $name,
            'codigo' => $code,
            'carga_horaria' => 80,
            'media_minima' => 6,
            'situacao' => 'ativa',
        ]);
    }

    private function createClass(string $name, string $room): Turma
    {
        return Turma::create([
            'nome' => $name,
            'serie' => 'Ensino Médio',
            'turno' => 'matutino',
            'sala' => $room,
            'ano_letivo' => 2026,
            'limite_alunos' => 30,
            'situacao' => 'ativa',
        ]);
    }

    private function createAllocation(
        Professor $professor,
        Disciplina $discipline,
        Turma $class,
    ): DisciplinaProfessor {
        return DisciplinaProfessor::create([
            'professor_id' => $professor->getKey(),
            'disciplina_id' => $discipline->getKey(),
            'turma_id' => $class->getKey(),
        ]);
    }
}

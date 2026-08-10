<?php

namespace Tests\Feature;

use App\Http\Controllers\AlocacaoController;
use App\Models\Disciplina;
use App\Models\DisciplinaProfessor;
use App\Models\Professor;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TeacherAllocationTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Route::has('alocacoes.index')) {
            Route::middleware(['web', 'auth', 'role:administrador,professor'])
                ->resource('alocacoes', AlocacaoController::class)
                ->except('show');
        }
    }

    public function test_management_user_can_create_and_find_a_teacher_allocation(): void
    {
        $administrator = $this->createUser('administrador');
        $professor = $this->createProfessor('Helena Souza');
        $disciplina = $this->createDisciplina('Biologia', 'BIO101');
        $turma = $this->createTurma('2º A');

        $this->actingAs($administrator)
            ->post(route('alocacoes.store'), $this->allocationData($professor, $disciplina, $turma))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('alocacoes.index'));

        $allocation = DisciplinaProfessor::query()->firstOrFail();

        $this->assertModelExists($allocation);

        $this->actingAs($administrator)
            ->get(route('alocacoes.index', ['busca' => 'Helena']))
            ->assertOk()
            ->assertSee('Helena Souza')
            ->assertSee('Biologia')
            ->assertSee('2º A');
    }

    public function test_duplicate_teacher_subject_and_class_combination_is_rejected(): void
    {
        $administrator = $this->createUser('administrador');
        $professor = $this->createProfessor('Carlos Lima');
        $disciplina = $this->createDisciplina('História', 'HIS101');
        $turma = $this->createTurma('1º B');
        $data = $this->allocationData($professor, $disciplina, $turma);

        DisciplinaProfessor::create($data);

        $this->actingAs($administrator)
            ->from(route('alocacoes.create'))
            ->post(route('alocacoes.store'), $data)
            ->assertRedirect(route('alocacoes.create'))
            ->assertSessionHasErrors([
                'turma_id' => 'Este professor já está alocado nesta disciplina e turma.',
            ]);

        $this->assertSame(1, DisciplinaProfessor::query()->count());
    }

    public function test_teacher_cannot_be_allocated_to_a_second_subject(): void
    {
        $administrator = $this->createUser('administrador');
        $professor = $this->createProfessor('Marta Nunes');
        $firstSubject = $this->createDisciplina('Química', 'QUI101');
        $secondSubject = $this->createDisciplina('Física', 'FIS101');
        $firstClass = $this->createTurma('1º A');
        $secondClass = $this->createTurma('2º B');

        DisciplinaProfessor::create(
            $this->allocationData($professor, $firstSubject, $firstClass)
        );

        $this->actingAs($administrator)
            ->from(route('alocacoes.create'))
            ->post(
                route('alocacoes.store'),
                $this->allocationData($professor, $secondSubject, $secondClass),
            )
            ->assertRedirect(route('alocacoes.create'))
            ->assertSessionHasErrors([
                'disciplina_id' => 'Este professor já está alocado em outra disciplina. Um professor pode atuar em apenas uma disciplina, mesmo em turmas diferentes.',
            ]);

        $this->assertSame(1, DisciplinaProfessor::query()->count());
    }

    public function test_teacher_can_repeat_the_same_subject_in_another_class(): void
    {
        $administrator = $this->createUser('administrador');
        $professor = $this->createProfessor('Lucas Rocha');
        $disciplina = $this->createDisciplina('Artes', 'ART101');
        $firstClass = $this->createTurma('1º C');
        $secondClass = $this->createTurma('2º C');

        DisciplinaProfessor::create(
            $this->allocationData($professor, $disciplina, $firstClass)
        );

        $this->actingAs($administrator)
            ->post(
                route('alocacoes.store'),
                $this->allocationData($professor, $disciplina, $secondClass),
            )
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('alocacoes.index'));

        $this->assertSame(2, DisciplinaProfessor::query()->count());
    }

    public function test_current_allocation_is_ignored_on_update_but_another_duplicate_is_rejected(): void
    {
        $administrator = $this->createUser('administrador');
        $firstProfessor = $this->createProfessor('Ana Torres');
        $secondProfessor = $this->createProfessor('Bruno Melo');
        $disciplina = $this->createDisciplina('Geografia', 'GEO101');
        $turma = $this->createTurma('3º C');

        $firstAllocation = DisciplinaProfessor::create(
            $this->allocationData($firstProfessor, $disciplina, $turma)
        );
        $secondAllocation = DisciplinaProfessor::create(
            $this->allocationData($secondProfessor, $disciplina, $turma)
        );

        $this->actingAs($administrator)
            ->put(route('alocacoes.update', $firstAllocation), $this->allocationData($firstProfessor, $disciplina, $turma))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('alocacoes.index'));

        $this->actingAs($administrator)
            ->from(route('alocacoes.edit', $secondAllocation))
            ->put(route('alocacoes.update', $secondAllocation), $this->allocationData($firstProfessor, $disciplina, $turma))
            ->assertRedirect(route('alocacoes.edit', $secondAllocation))
            ->assertSessionHasErrors('turma_id');

        $this->actingAs($administrator)
            ->from(route('alocacoes.edit', $secondAllocation))
            ->patch(route('alocacoes.update', $secondAllocation), [
                'professor_id' => $firstProfessor->getKey(),
            ])
            ->assertRedirect(route('alocacoes.edit', $secondAllocation))
            ->assertSessionHasErrors([
                'turma_id' => 'Este professor já está alocado nesta disciplina e turma.',
            ]);

        $this->assertSame($secondProfessor->getKey(), $secondAllocation->refresh()->professor_id);
    }

    public function test_partial_update_cannot_assign_a_second_subject_to_teacher(): void
    {
        $administrator = $this->createUser('administrador');
        $firstProfessor = $this->createProfessor('Paulo Freitas');
        $secondProfessor = $this->createProfessor('Renata Alves');
        $firstSubject = $this->createDisciplina('Sociologia', 'SOC101');
        $secondSubject = $this->createDisciplina('Filosofia', 'FIL101');
        $firstClass = $this->createTurma('1º D');
        $secondClass = $this->createTurma('2º D');

        DisciplinaProfessor::create(
            $this->allocationData($firstProfessor, $firstSubject, $firstClass)
        );
        $allocation = DisciplinaProfessor::create(
            $this->allocationData($secondProfessor, $secondSubject, $secondClass)
        );

        $this->actingAs($administrator)
            ->from(route('alocacoes.edit', $allocation))
            ->patch(route('alocacoes.update', $allocation), [
                'professor_id' => $firstProfessor->getKey(),
            ])
            ->assertRedirect(route('alocacoes.edit', $allocation))
            ->assertSessionHasErrors([
                'disciplina_id' => 'Este professor já está alocado em outra disciplina. Um professor pode atuar em apenas uma disciplina, mesmo em turmas diferentes.',
            ]);

        $this->assertSame($secondProfessor->getKey(), $allocation->refresh()->professor_id);
    }

    public function test_form_only_offers_active_professors_subjects_and_classes(): void
    {
        $administrator = $this->createUser('administrador');
        $activeProfessor = $this->createProfessor('Docente Ativo');
        $inactiveProfessor = $this->createProfessor('Docente Inativo', 'inativo');
        $activeSubject = $this->createDisciplina('Matemática', 'MAT101');
        $inactiveSubject = $this->createDisciplina('Disciplina Arquivada', 'ARQ101', 'inativa');
        $activeClass = $this->createTurma('Turma Ativa');
        $inactiveClass = $this->createTurma('Turma Encerrada', 'inativa');

        $this->actingAs($administrator)
            ->get(route('alocacoes.create'))
            ->assertOk()
            ->assertSee($activeProfessor->nome)
            ->assertSee($activeSubject->nome)
            ->assertSee($activeClass->nome)
            ->assertDontSee($inactiveProfessor->nome)
            ->assertDontSee($inactiveSubject->nome)
            ->assertDontSee($inactiveClass->nome);

        $this->actingAs($administrator)
            ->post(route('alocacoes.store'), $this->allocationData($inactiveProfessor, $activeSubject, $activeClass))
            ->assertSessionHasErrors([
                'professor_id' => 'Selecione um professor ativo.',
            ]);
    }

    public function test_student_cannot_access_teacher_allocations(): void
    {
        $student = $this->createUser('aluno');

        $this->actingAs($student)
            ->get(route('alocacoes.index'))
            ->assertForbidden();
    }

    private function createUser(string $role): User
    {
        return User::factory()->create([
            'tipo_usuario' => $role,
            'situacao' => 'ativo',
        ]);
    }

    private function createProfessor(string $name, string $status = 'ativo'): Professor
    {
        $user = $this->createUser('professor');

        return Professor::create([
            'user_id' => $user->getKey(),
            'nome' => $name,
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'email' => fake()->unique()->safeEmail(),
            'especialidade' => 'Educação básica',
            'situacao' => $status,
        ]);
    }

    private function createDisciplina(string $name, string $code, string $status = 'ativa'): Disciplina
    {
        return Disciplina::create([
            'nome' => $name,
            'codigo' => $code,
            'carga_horaria' => 80,
            'media_minima' => 6,
            'situacao' => $status,
        ]);
    }

    private function createTurma(string $name, string $status = 'ativa'): Turma
    {
        return Turma::create([
            'nome' => $name,
            'serie' => 'Ensino Médio',
            'turno' => 'matutino',
            'sala' => '101',
            'ano_letivo' => 2026,
            'limite_alunos' => 30,
            'situacao' => $status,
        ]);
    }

    /** @return array{professor_id: int, disciplina_id: int, turma_id: int} */
    private function allocationData(Professor $professor, Disciplina $disciplina, Turma $turma): array
    {
        return [
            'professor_id' => $professor->getKey(),
            'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(),
        ];
    }
}

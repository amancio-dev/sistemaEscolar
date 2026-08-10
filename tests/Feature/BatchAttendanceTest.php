<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\DisciplinaProfessor;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Professor;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_attendance_loads_active_students_and_saves_the_whole_class(): void
    {
        $administrator = $this->createUser('administrador');
        [, $professor, $turma, $disciplina, $alocacao] = $this->createAcademicContext();
        $firstStudent = $this->createStudent($turma, 'Ana Lima', '2026001');
        $secondStudent = $this->createStudent($turma, 'Bruno Reis', '2026002');
        $inactiveStudent = $this->createStudent($turma, 'Carla Souza', '2026003', 'trancada');
        $date = today()->subDay()->toDateString();

        $this->actingAs($administrator)
            ->get(route('frequencias.chamada', [
                'alocacao_id' => $alocacao->getKey(),
                'data_aula' => $date,
            ]))
            ->assertOk()
            ->assertSeeInOrder(['Ana Lima', 'Bruno Reis'])
            ->assertDontSee('Carla Souza');

        $this->actingAs($administrator)
            ->post(route('frequencias.chamada.store'), [
                'alocacao_id' => $alocacao->getKey(),
                'data_aula' => $date,
                'frequencias' => [
                    ['aluno_id' => $firstStudent->getKey(), 'situacao' => 'presente'],
                    [
                        'aluno_id' => $secondStudent->getKey(),
                        'situacao' => 'justificada',
                        'justificativa' => 'Consulta médica',
                    ],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('frequencias.chamada', [
                'alocacao_id' => $alocacao->getKey(),
                'data_aula' => $date,
            ]));

        $this->assertSame(2, Frequencia::query()->count());
        $this->assertDatabaseHas('frequencias', [
            'aluno_id' => $firstStudent->getKey(),
            'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(),
            'professor_id' => $professor->getKey(),
            'situacao' => 'presente',
        ]);
        $this->assertDatabaseHas('frequencias', [
            'aluno_id' => $secondStudent->getKey(),
            'situacao' => 'justificada',
            'justificativa' => 'Consulta médica',
        ]);
        $this->assertDatabaseMissing('frequencias', ['aluno_id' => $inactiveStudent->getKey()]);
    }

    public function test_saving_the_same_call_again_updates_instead_of_duplicating_records(): void
    {
        $administrator = $this->createUser('administrador');
        [, , $turma, , $alocacao] = $this->createAcademicContext();
        $student = $this->createStudent($turma, 'Ana Lima', '2026004');
        $date = today()->subDays(2)->toDateString();

        foreach (['ausente', 'presente'] as $status) {
            $this->actingAs($administrator)
                ->post(route('frequencias.chamada.store'), [
                    'alocacao_id' => $alocacao->getKey(),
                    'data_aula' => $date,
                    'frequencias' => [[
                        'aluno_id' => $student->getKey(),
                        'situacao' => $status,
                    ]],
                ])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(1, Frequencia::query()->count());
        $frequencia = Frequencia::query()->sole();
        $this->assertSame('presente', $frequencia->situacao);
        $this->assertSame($date, $frequencia->getRawOriginal('data_aula'));
    }

    public function test_batch_attendance_rejects_students_outside_the_active_class_list(): void
    {
        $administrator = $this->createUser('administrador');
        [, , $turma, , $alocacao] = $this->createAcademicContext();
        $activeStudent = $this->createStudent($turma, 'Ana Lima', '2026005');
        $otherClass = $this->createClass('302B');
        $outsideStudent = $this->createStudent($otherClass, 'Pessoa externa', '2026006');

        $this->actingAs($administrator)
            ->post(route('frequencias.chamada.store'), [
                'alocacao_id' => $alocacao->getKey(),
                'data_aula' => today()->toDateString(),
                'frequencias' => [
                    ['aluno_id' => $activeStudent->getKey(), 'situacao' => 'presente'],
                    ['aluno_id' => $outsideStudent->getKey(), 'situacao' => 'presente'],
                ],
            ])
            ->assertSessionHasErrors([
                'frequencias' => 'A chamada deve conter exatamente os alunos com matrícula ativa nesta turma.',
            ]);

        $this->assertSame(0, Frequencia::query()->count());
    }

    public function test_same_student_subject_and_date_are_kept_separate_between_classes(): void
    {
        $administrator = $this->createUser('administrador');
        [, $firstProfessor, $firstClass, $subject, $firstAllocation] = $this->createAcademicContext('Primeiro docente', '301A');
        [, $secondProfessor, $secondClass] = $this->createAcademicContext('Segundo docente', '302B');
        $secondAllocation = DisciplinaProfessor::create([
            'professor_id' => $secondProfessor->getKey(),
            'disciplina_id' => $subject->getKey(),
            'turma_id' => $secondClass->getKey(),
        ]);
        $student = $this->createStudent($firstClass, 'Aluno em duas turmas', '2026010');
        Matricula::create([
            'aluno_id' => $student->getKey(),
            'turma_id' => $secondClass->getKey(),
            'data_matricula' => '2026-02-01',
            'ano_letivo' => 2026,
            'situacao' => 'ativa',
        ]);
        $date = today()->subDay()->toDateString();

        foreach ([
            [$firstAllocation, 'presente'],
            [$secondAllocation, 'ausente'],
        ] as [$allocation, $status]) {
            $this->actingAs($administrator)
                ->post(route('frequencias.chamada.store'), [
                    'alocacao_id' => $allocation->getKey(),
                    'data_aula' => $date,
                    'frequencias' => [[
                        'aluno_id' => $student->getKey(),
                        'situacao' => $status,
                    ]],
                ])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(2, Frequencia::query()->count());
        $this->assertDatabaseHas('frequencias', [
            'turma_id' => $firstClass->getKey(),
            'professor_id' => $firstProfessor->getKey(),
            'situacao' => 'presente',
        ]);
        $this->assertDatabaseHas('frequencias', [
            'turma_id' => $secondClass->getKey(),
            'professor_id' => $secondProfessor->getKey(),
            'situacao' => 'ausente',
        ]);
    }

    public function test_professor_can_only_access_and_save_own_allocations(): void
    {
        [$teacherUser, , $turma, , $ownAllocation] = $this->createAcademicContext('Docente titular');
        [, , $otherClass, , $otherAllocation] = $this->createAcademicContext('Outro docente', '302C');
        $student = $this->createStudent($turma, 'Ana Lima', '2026007');
        $this->createStudent($otherClass, 'Bruno Reis', '2026008');

        $this->actingAs($teacherUser)
            ->get(route('frequencias.chamada', ['alocacao_id' => $ownAllocation->getKey()]))
            ->assertOk()
            ->assertSee('Ana Lima');

        $this->actingAs($teacherUser)
            ->get(route('frequencias.chamada', ['alocacao_id' => $otherAllocation->getKey()]))
            ->assertForbidden();

        $this->actingAs($teacherUser)
            ->post(route('frequencias.chamada.store'), [
                'alocacao_id' => $otherAllocation->getKey(),
                'data_aula' => today()->toDateString(),
                'frequencias' => [['aluno_id' => $student->getKey(), 'situacao' => 'presente']],
            ])
            ->assertSessionHasErrors('alocacao_id');
    }

    /** @return array{User, Professor, Turma, Disciplina, DisciplinaProfessor} */
    private function createAcademicContext(string $teacherName = 'Professor Teste', string $className = '301A'): array
    {
        $teacherUser = $this->createUser('professor');
        $professor = Professor::create([
            'user_id' => $teacherUser->getKey(),
            'nome' => $teacherName,
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'email' => fake()->unique()->safeEmail(),
            'situacao' => 'ativo',
        ]);
        $turma = $this->createClass($className);
        $disciplina = Disciplina::create([
            'nome' => 'Matemática '.$className,
            'codigo' => 'MAT'.$turma->getKey(),
            'carga_horaria' => 80,
            'media_minima' => 6,
            'situacao' => 'ativa',
        ]);
        $alocacao = DisciplinaProfessor::create([
            'professor_id' => $professor->getKey(),
            'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(),
        ]);

        return [$teacherUser, $professor, $turma, $disciplina, $alocacao];
    }

    private function createClass(string $name): Turma
    {
        return Turma::create([
            'nome' => $name,
            'serie' => 'Ensino Médio',
            'turno' => 'matutino',
            'sala' => fake()->numerify('###'),
            'ano_letivo' => 2026,
            'limite_alunos' => 30,
            'situacao' => 'ativa',
        ]);
    }

    private function createStudent(Turma $turma, string $name, string $registration, string $status = 'ativa'): Aluno
    {
        $user = $this->createUser('aluno');
        $student = Aluno::create([
            'user_id' => $user->getKey(),
            'nome' => $name,
            'numero_matricula' => $registration,
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'data_nascimento' => '2010-01-01',
            'situacao' => 'ativo',
        ]);
        Matricula::create([
            'aluno_id' => $student->getKey(),
            'turma_id' => $turma->getKey(),
            'data_matricula' => '2026-02-01',
            'ano_letivo' => 2026,
            'situacao' => $status,
        ]);

        return $student;
    }

    private function createUser(string $role): User
    {
        return User::factory()->create([
            'tipo_usuario' => $role,
            'situacao' => 'ativo',
        ]);
    }
}

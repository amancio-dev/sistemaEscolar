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

class AcademicRecordIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_requires_active_enrollment_and_teacher_assignment(): void
    {
        $administrator = $this->createUser('administrador');
        [, $professor, $turma, $disciplina] = $this->createContext();
        $student = $this->createStudent('Aluno sem matrícula', '2026101');

        $this->actingAs($administrator)
            ->post(route('notas.store'), [
                'aluno_id' => $student->getKey(),
                'disciplina_id' => $disciplina->getKey(),
                'turma_id' => $turma->getKey(),
                'professor_id' => $professor->getKey(),
                'periodo' => 'primeiro_bimestre',
                'avaliacao' => 'Prova',
                'valor' => 8,
            ])
            ->assertSessionHasErrors([
                'aluno_id' => 'O aluno precisa ter matrícula ativa na turma selecionada.',
                'professor_id' => 'O professor não está alocado nesta disciplina e turma.',
            ]);
    }

    public function test_professor_cannot_record_attendance_in_another_teachers_name(): void
    {
        [$teacherUser] = $this->createContext('Professor autenticado', '301A');
        [, $otherProfessor, $otherClass, $otherSubject] = $this->createContext('Outro professor', '302B', true);
        $student = $this->createStudent('Aluno da outra turma', '2026102');
        Matricula::create([
            'aluno_id' => $student->getKey(),
            'turma_id' => $otherClass->getKey(),
            'data_matricula' => '2026-02-01',
            'ano_letivo' => 2026,
            'situacao' => 'ativa',
        ]);

        $this->actingAs($teacherUser)
            ->post(route('frequencias.store'), [
                'aluno_id' => $student->getKey(),
                'disciplina_id' => $otherSubject->getKey(),
                'turma_id' => $otherClass->getKey(),
                'professor_id' => $otherProfessor->getKey(),
                'data_aula' => today()->toDateString(),
                'situacao' => 'presente',
            ])
            ->assertSessionHasErrors([
                'professor_id' => 'Você só pode registrar frequências em seu próprio nome.',
            ]);
    }

    public function test_professor_only_sees_own_attendance_records(): void
    {
        [$teacherUser, $professor, $turma, $disciplina] = $this->createContext('Professor autenticado', '301A', true);
        [, $otherProfessor, $otherClass, $otherSubject] = $this->createContext('Outro professor', '302B', true);
        $ownStudent = $this->createStudent('Aluno visível', '2026103');
        $otherStudent = $this->createStudent('Aluno reservado', '2026104');

        Frequencia::create([
            'aluno_id' => $ownStudent->getKey(),
            'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(),
            'professor_id' => $professor->getKey(),
            'data_aula' => today()->subDay(),
            'situacao' => 'presente',
        ]);
        $otherAttendance = Frequencia::create([
            'aluno_id' => $otherStudent->getKey(),
            'disciplina_id' => $otherSubject->getKey(),
            'turma_id' => $otherClass->getKey(),
            'professor_id' => $otherProfessor->getKey(),
            'data_aula' => today()->subDay(),
            'situacao' => 'presente',
        ]);

        $this->actingAs($teacherUser)
            ->get(route('frequencias.index'))
            ->assertOk()
            ->assertSee('Aluno visível')
            ->assertDontSee('Aluno reservado');

        $this->actingAs($teacherUser)
            ->delete(route('frequencias.destroy', $otherAttendance))
            ->assertForbidden();

        $this->assertModelExists($otherAttendance);
    }

    /** @return array{User, Professor, Turma, Disciplina} */
    private function createContext(string $teacherName = 'Professor', string $className = '301A', bool $allocate = false): array
    {
        $teacherUser = $this->createUser('professor');
        $professor = Professor::create([
            'user_id' => $teacherUser->getKey(),
            'nome' => $teacherName,
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'email' => fake()->unique()->safeEmail(),
            'situacao' => 'ativo',
        ]);
        $turma = Turma::create([
            'nome' => $className,
            'serie' => 'Ensino Médio',
            'turno' => 'matutino',
            'sala' => fake()->numerify('###'),
            'ano_letivo' => 2026,
            'limite_alunos' => 30,
            'situacao' => 'ativa',
        ]);
        $disciplina = Disciplina::create([
            'nome' => 'Disciplina '.$className,
            'codigo' => 'D'.$turma->getKey(),
            'carga_horaria' => 80,
            'media_minima' => 6,
            'situacao' => 'ativa',
        ]);

        if ($allocate) {
            DisciplinaProfessor::create([
                'professor_id' => $professor->getKey(),
                'disciplina_id' => $disciplina->getKey(),
                'turma_id' => $turma->getKey(),
            ]);
        }

        return [$teacherUser, $professor, $turma, $disciplina];
    }

    private function createStudent(string $name, string $registration): Aluno
    {
        $user = $this->createUser('aluno');

        return Aluno::create([
            'user_id' => $user->getKey(),
            'nome' => $name,
            'numero_matricula' => $registration,
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'data_nascimento' => '2010-01-01',
            'situacao' => 'ativo',
        ]);
    }

    private function createUser(string $role): User
    {
        return User::factory()->create([
            'tipo_usuario' => $role,
            'situacao' => 'ativo',
        ]);
    }
}

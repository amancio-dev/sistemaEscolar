<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Frequencia;
use App\Models\Nota;
use App\Models\Professor;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_management_routes_even_by_direct_url(): void
    {
        [$user] = $this->createStudent('João Aluno', 'joao@escola.com', '111.222.333-44', '2026001');

        $this->actingAs($user)->get('/alunos')->assertForbidden();
        $this->actingAs($user)->get('/frequencias/create')->assertForbidden();
        $this->actingAs($user)->post('/notas', [])->assertForbidden();
    }

    public function test_student_portal_only_displays_records_from_authenticated_student(): void
    {
        [$user, $aluno] = $this->createStudent('João Aluno', 'joao@escola.com', '111.222.333-44', '2026001');
        [, $outroAluno] = $this->createStudent('Maria Aluna', 'maria@escola.com', '222.333.444-55', '2026002');
        [$professor, $turma, $disciplina] = $this->createAcademicContext();

        Nota::create([
            'aluno_id' => $aluno->getKey(), 'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(), 'professor_id' => $professor->getKey(),
            'periodo' => 'primeiro_bimestre', 'avaliacao' => 'Avaliação própria', 'valor' => 8,
        ]);
        Nota::create([
            'aluno_id' => $outroAluno->getKey(), 'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(), 'professor_id' => $professor->getKey(),
            'periodo' => 'primeiro_bimestre', 'avaliacao' => 'Avaliação privada de outra aluna', 'valor' => 4,
        ]);

        Frequencia::create([
            'aluno_id' => $aluno->getKey(), 'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(), 'professor_id' => $professor->getKey(),
            'data_aula' => '2026-08-01', 'situacao' => 'justificada', 'justificativa' => 'Motivo próprio',
        ]);
        Frequencia::create([
            'aluno_id' => $outroAluno->getKey(), 'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(), 'professor_id' => $professor->getKey(),
            'data_aula' => '2026-08-01', 'situacao' => 'justificada', 'justificativa' => 'Dado privado de outra aluna',
        ]);

        $this->actingAs($user)
            ->get(route('portal.notas'))
            ->assertOk()
            ->assertSee('Avaliação própria')
            ->assertDontSee('Avaliação privada de outra aluna');

        $this->actingAs($user)
            ->get(route('portal.frequencias'))
            ->assertOk()
            ->assertSee('Motivo próprio')
            ->assertDontSee('Dado privado de outra aluna');
    }

    public function test_frequency_management_page_calculates_presence_and_absence_per_student(): void
    {
        $admin = User::factory()->create(['tipo_usuario' => 'administrador', 'situacao' => 'ativo']);
        [, $aluno] = $this->createStudent('João Aluno', 'joao@escola.com', '111.222.333-44', '2026001');
        [$professor, $turma, $disciplina] = $this->createAcademicContext();

        foreach ([
            ['2026-08-01', 'presente'],
            ['2026-08-02', 'presente'],
            ['2026-08-03', 'ausente'],
        ] as [$data, $situacao]) {
            Frequencia::create([
                'aluno_id' => $aluno->getKey(), 'disciplina_id' => $disciplina->getKey(),
                'turma_id' => $turma->getKey(), 'professor_id' => $professor->getKey(),
                'data_aula' => $data, 'situacao' => $situacao,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('frequencias.index'))
            ->assertOk()
            ->assertSee('Quantidade de presenças e faltas')
            ->assertViewHas('resumoPorAluno', function ($items) use ($aluno): bool {
                $summary = $items->first(fn ($item) => (int) $item->aluno_id === (int) $aluno->getKey());

                return $summary
                    && (int) $summary->presencas === 2
                    && (int) $summary->faltas === 1
                    && (int) $summary->total === 3;
            });
    }

    public function test_duplicate_attendance_for_same_student_subject_and_date_is_rejected(): void
    {
        $admin = User::factory()->create(['tipo_usuario' => 'administrador', 'situacao' => 'ativo']);
        [, $aluno] = $this->createStudent('João Aluno', 'joao@escola.com', '111.222.333-44', '2026001');
        [$professor, $turma, $disciplina] = $this->createAcademicContext();

        $data = [
            'aluno_id' => $aluno->getKey(),
            'disciplina_id' => $disciplina->getKey(),
            'turma_id' => $turma->getKey(),
            'professor_id' => $professor->getKey(),
            'data_aula' => '2026-08-01',
            'situacao' => 'presente',
        ];

        Frequencia::create($data);

        $this->actingAs($admin)
            ->from(route('frequencias.create'))
            ->post(route('frequencias.store'), $data)
            ->assertRedirect(route('frequencias.create'))
            ->assertSessionHasErrors([
                'data_aula' => 'Já existe uma frequência para este aluno, disciplina e data.',
            ]);
    }

    /** @return array{User, Aluno} */
    private function createStudent(string $name, string $email, string $cpf, string $registration): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'cpf' => $cpf,
            'tipo_usuario' => 'aluno',
            'situacao' => 'ativo',
        ]);

        $aluno = Aluno::create([
            'user_id' => $user->getKey(),
            'nome' => $name,
            'numero_matricula' => $registration,
            'cpf' => $cpf,
            'data_nascimento' => '2010-01-01',
            'email' => $email,
            'situacao' => 'ativo',
        ]);

        return [$user, $aluno];
    }

    /** @return array{Professor, Turma, Disciplina} */
    private function createAcademicContext(): array
    {
        $professorUser = User::factory()->create([
            'email' => 'professor@escola.com',
            'tipo_usuario' => 'professor',
            'situacao' => 'ativo',
        ]);

        $professor = Professor::create([
            'user_id' => $professorUser->getKey(),
            'nome' => 'Professor Teste',
            'cpf' => '999.888.777-66',
            'email' => 'professor.academico@escola.com',
            'situacao' => 'ativo',
        ]);

        $turma = Turma::create([
            'nome' => '301A', 'serie' => 'Ensino Médio', 'turno' => 'matutino',
            'sala' => '101', 'ano_letivo' => 2026, 'limite_alunos' => 30,
            'professor_responsavel_id' => $professor->getKey(), 'situacao' => 'ativa',
        ]);

        $disciplina = Disciplina::create([
            'nome' => 'Matemática', 'codigo' => 'MAT101', 'carga_horaria' => 80,
            'media_minima' => 6, 'situacao' => 'ativa',
        ]);

        return [$professor, $turma, $disciplina];
    }
}

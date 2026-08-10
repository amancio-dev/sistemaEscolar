<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MatriculaIntegrityTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $administrator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->administrator = User::factory()->create([
            'tipo_usuario' => 'administrador',
            'situacao' => 'ativo',
        ]);
    }

    public function test_duplicate_enrollment_is_rejected_but_the_record_can_be_updated_without_conflicting_with_itself(): void
    {
        $aluno = $this->createStudent('2026001');
        $turma = $this->createClass();
        $matricula = $this->createEnrollment($aluno, $turma);

        $this->actingAs($this->administrator)
            ->from(route('matriculas.create'))
            ->post(route('matriculas.store'), $this->enrollmentData($aluno, $turma))
            ->assertRedirect(route('matriculas.create'))
            ->assertSessionHasErrors([
                'aluno_id' => 'Este aluno já possui matrícula nesta turma para o ano letivo informado.',
            ]);

        $this->actingAs($this->administrator)
            ->put(route('matriculas.update', $matricula), [
                'observacoes' => 'Matrícula conferida pela secretaria.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('matriculas.index'));

        $this->assertSame('Matrícula conferida pela secretaria.', $matricula->refresh()->observacoes);
    }

    public function test_enrollment_year_must_match_the_selected_class_year(): void
    {
        $aluno = $this->createStudent('2026002');
        $turma = $this->createClass();
        $data = $this->enrollmentData($aluno, $turma);
        $data['ano_letivo'] = 2025;

        $this->actingAs($this->administrator)
            ->post(route('matriculas.store'), $data)
            ->assertSessionHasErrors([
                'ano_letivo' => 'O ano letivo da matrícula deve ser o mesmo da turma selecionada (2026).',
            ]);

        $this->assertSame(0, Matricula::query()->count());
    }

    public function test_active_enrollment_cannot_be_assigned_to_an_inactive_class(): void
    {
        $aluno = $this->createStudent('2026003');
        $turma = $this->createClass(['situacao' => 'inativa']);

        $this->actingAs($this->administrator)
            ->post(route('matriculas.store'), $this->enrollmentData($aluno, $turma))
            ->assertSessionHasErrors([
                'turma_id' => 'Não é possível manter uma matrícula ativa em uma turma inativa ou concluída.',
            ]);

        $inactiveData = $this->enrollmentData($aluno, $turma);
        $inactiveData['situacao'] = 'cancelada';

        $this->actingAs($this->administrator)
            ->post(route('matriculas.store'), $inactiveData)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('matriculas.index'));

        $this->assertSame('cancelada', Matricula::query()->sole()->situacao);
    }

    public function test_class_capacity_counts_only_other_active_enrollments(): void
    {
        $firstStudent = $this->createStudent('2026004');
        $secondStudent = $this->createStudent('2026005');
        $thirdStudent = $this->createStudent('2026006');
        $turma = $this->createClass(['limite_alunos' => 1]);
        $activeEnrollment = $this->createEnrollment($firstStudent, $turma);

        $this->actingAs($this->administrator)
            ->post(route('matriculas.store'), $this->enrollmentData($secondStudent, $turma))
            ->assertSessionHasErrors([
                'turma_id' => 'A turma selecionada está lotada (1 de 1 vagas ocupadas).',
            ]);

        $this->actingAs($this->administrator)
            ->put(route('matriculas.update', $activeEnrollment), [
                'observacoes' => 'Atualização permitida na própria vaga.',
            ])
            ->assertSessionHasNoErrors();

        $inactiveEnrollment = $this->createEnrollment($thirdStudent, $turma, ['situacao' => 'trancada']);

        $this->actingAs($this->administrator)
            ->put(route('matriculas.update', $inactiveEnrollment), ['situacao' => 'ativa'])
            ->assertSessionHasErrors([
                'turma_id' => 'A turma selecionada está lotada (1 de 1 vagas ocupadas).',
            ]);

        $this->assertSame('trancada', $inactiveEnrollment->refresh()->situacao);
    }

    public function test_create_form_lists_available_places_from_an_aggregate_count(): void
    {
        $turma = $this->createClass(['nome' => 'Turma Alfa', 'limite_alunos' => 2]);
        $this->createEnrollment($this->createStudent('2026007'), $turma);
        $this->createEnrollment($this->createStudent('2026008'), $turma, ['situacao' => 'trancada']);

        $this->actingAs($this->administrator)
            ->get(route('matriculas.create'))
            ->assertOk()
            ->assertSeeInOrder([
                'Turma Alfa',
                'Ensino Médio',
                '2026',
                '1',
                'vaga livre',
                'Ativa',
            ]);
    }

    private function createStudent(string $registration): Aluno
    {
        $user = User::factory()->create([
            'tipo_usuario' => 'aluno',
            'situacao' => 'ativo',
        ]);

        return Aluno::create([
            'user_id' => $user->getKey(),
            'numero_matricula' => $registration,
            'nome' => "Aluno {$registration}",
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'data_nascimento' => '2010-01-01',
            'situacao' => 'ativo',
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function createClass(array $overrides = []): Turma
    {
        return Turma::create(array_replace([
            'nome' => '301A',
            'serie' => 'Ensino Médio',
            'turno' => 'matutino',
            'sala' => '101',
            'ano_letivo' => 2026,
            'limite_alunos' => 30,
            'situacao' => 'ativa',
        ], $overrides));
    }

    /** @param array<string, mixed> $overrides */
    private function createEnrollment(Aluno $aluno, Turma $turma, array $overrides = []): Matricula
    {
        return Matricula::create(array_replace($this->enrollmentData($aluno, $turma), $overrides));
    }

    /** @return array<string, mixed> */
    private function enrollmentData(Aluno $aluno, Turma $turma): array
    {
        return [
            'aluno_id' => $aluno->getKey(),
            'turma_id' => $turma->getKey(),
            'data_matricula' => '2026-01-15',
            'ano_letivo' => 2026,
            'situacao' => 'ativa',
            'observacoes' => null,
        ];
    }
}

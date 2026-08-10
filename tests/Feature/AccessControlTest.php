<?php

namespace Tests\Feature;

use App\Models\Disciplina;
use App\Models\DisciplinaProfessor;
use App\Models\Professor;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_cannot_manage_institutional_records(): void
    {
        $teacher = User::factory()->create([
            'tipo_usuario' => 'professor',
            'situacao' => 'ativo',
        ]);

        $this->actingAs($teacher)->get(route('alunos.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('professores.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('matriculas.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('alocacoes.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('turmas.create'))->assertForbidden();
    }

    public function test_professor_only_sees_assigned_classes(): void
    {
        $teacherUser = User::factory()->create([
            'tipo_usuario' => 'professor',
            'situacao' => 'ativo',
        ]);
        $professor = Professor::create([
            'user_id' => $teacherUser->getKey(),
            'nome' => 'Docente Titular',
            'cpf' => '111.222.333-44',
            'email' => 'docente@escola.test',
            'situacao' => 'ativo',
        ]);
        $ownClass = $this->createClass('Turma própria');
        $otherClass = $this->createClass('Turma de outro docente');
        $subject = Disciplina::create([
            'nome' => 'Matemática',
            'codigo' => 'MAT101',
            'carga_horaria' => 80,
            'media_minima' => 6,
            'situacao' => 'ativa',
        ]);
        DisciplinaProfessor::create([
            'professor_id' => $professor->getKey(),
            'disciplina_id' => $subject->getKey(),
            'turma_id' => $ownClass->getKey(),
        ]);

        $this->actingAs($teacherUser)
            ->get(route('turmas.index'))
            ->assertOk()
            ->assertSee('Turma própria')
            ->assertDontSee('Turma de outro docente');

        $this->actingAs($teacherUser)->get(route('turmas.show', $ownClass))->assertOk();
        $this->actingAs($teacherUser)->get(route('turmas.show', $otherClass))->assertNotFound();

        $this->actingAs($teacherUser)
            ->getJson(route('api.turmas.show', $ownClass))
            ->assertOk()
            ->assertJsonPath('data.alocacoes.0.professor.nome', 'Docente Titular')
            ->assertJsonMissing(['cpf' => '111.222.333-44'])
            ->assertJsonMissing(['email' => 'docente@escola.test']);
    }

    public function test_authenticated_web_session_can_use_the_api(): void
    {
        $administrator = User::factory()->create([
            'email' => 'admin.api@example.test',
            'password' => Hash::make('SenhaApi123'),
            'tipo_usuario' => 'administrador',
            'situacao' => 'ativo',
        ]);
        $this->createClass('Turma disponível na API');

        $this->post(route('login.store'), [
            'email' => $administrator->email,
            'tipo_usuario' => 'administrador',
            'password' => 'SenhaApi123',
        ])->assertRedirect(route('inicio'));

        $this->getJson(route('api.turmas.index'))
            ->assertOk()
            ->assertJsonPath('data.data.0.nome', 'Turma disponível na API');
    }

    public function test_inactivated_user_is_logged_out_on_the_next_request(): void
    {
        $administrator = User::factory()->create([
            'tipo_usuario' => 'administrador',
            'situacao' => 'inativo',
        ]);

        $this->actingAs($administrator)
            ->get(route('inicio'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Sua conta está inativa. Entre em contato com a administração.');

        $this->assertGuest();
    }

    public function test_inactivated_api_user_receives_json_even_without_accept_header(): void
    {
        $administrator = User::factory()->create([
            'tipo_usuario' => 'administrador',
            'situacao' => 'inativo',
        ]);

        $this->actingAs($administrator)
            ->get('/api/turmas')
            ->assertForbidden()
            ->assertHeader('content-type', 'application/json');

        $this->assertGuest();
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
}

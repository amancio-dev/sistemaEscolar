<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_contains_profile_selector_and_credential_fields(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="tipo_usuario"', false)
            ->assertSee('value="administrador"', false)
            ->assertSee('value="professor"', false)
            ->assertSee('value="aluno"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="cpf"', false);
    }

    public function test_administrator_can_login_with_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('senha-administrativa'),
            'tipo_usuario' => 'administrador',
            'situacao' => 'ativo',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'tipo_usuario' => 'administrador',
            'password' => 'senha-administrativa',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('inicio'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_professor_can_login_with_formatted_cpf(): void
    {
        [$user, $professor] = $this->createProfessorAccount('123.456.789-00');

        $this->post(route('login.store'), [
            'email' => $professor->email,
            'tipo_usuario' => 'professor',
            'cpf' => '123.456.789-00',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('inicio'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_student_can_login_with_unformatted_cpf(): void
    {
        [$user] = $this->createStudentAccount('111.222.333-44');

        $this->post(route('login.store'), [
            'email' => $user->email,
            'tipo_usuario' => 'aluno',
            'cpf' => '11122233344',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('inicio'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_an_incorrect_profile(): void
    {
        [$user] = $this->createProfessorAccount('123.456.789-00');

        $this->post(route('login.store'), [
            'email' => $user->email,
            'tipo_usuario' => 'aluno',
            'cpf' => '123.456.789-00',
        ])
            ->assertSessionHasErrors([
                'email' => 'E-mail, credencial ou perfil incorretos.',
            ]);

        $this->assertGuest();
    }

    public function test_login_validates_credentials_conditionally_for_each_profile(): void
    {
        $this->post(route('login.store'), [
            'email' => 'admin@example.test',
            'tipo_usuario' => 'administrador',
        ])
            ->assertSessionHasErrors('password')
            ->assertSessionDoesntHaveErrors('cpf');

        $this->post(route('login.store'), [
            'email' => 'professor@example.test',
            'tipo_usuario' => 'professor',
        ])
            ->assertSessionHasErrors('cpf')
            ->assertSessionDoesntHaveErrors('password');

        $this->post(route('login.store'), [
            'email' => 'aluno@example.test',
            'tipo_usuario' => 'aluno',
        ])
            ->assertSessionHasErrors('cpf')
            ->assertSessionDoesntHaveErrors('password');

        $this->post(route('login.store'), [
            'email' => 'gestor@example.test',
            'tipo_usuario' => 'gestor',
        ])->assertSessionHasErrors('tipo_usuario');

        $this->assertGuest();
    }

    public function test_incorrect_cpf_is_not_repopulated(): void
    {
        [$user] = $this->createProfessorAccount('123.456.789-00');

        $this->post(route('login.store'), [
            'email' => $user->email,
            'tipo_usuario' => 'professor',
            'cpf' => '987.654.321-00',
        ])
            ->assertSessionHasErrors([
                'email' => 'E-mail, credencial ou perfil incorretos.',
            ])
            ->assertSessionHasInput('email', $user->email)
            ->assertSessionHasInput('tipo_usuario', 'professor')
            ->assertSessionMissingInput('cpf')
            ->assertSessionMissingInput('password');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login_with_correct_cpf(): void
    {
        [$user] = $this->createStudentAccount('111.222.333-44', 'inativo');

        $this->post(route('login.store'), [
            'email' => $user->email,
            'tipo_usuario' => 'aluno',
            'cpf' => '111.222.333-44',
        ])
            ->assertSessionHasErrors([
                'email' => 'Este cadastro está inativo. Procure a secretaria acadêmica.',
            ])
            ->assertSessionMissingInput('cpf');

        $this->assertGuest();
    }

    public function test_legacy_academic_account_is_backfilled_before_login(): void
    {
        $user = User::factory()->create([
            'email' => 'legado@example.test',
            'cpf' => null,
            'password' => Hash::make('senha-antiga'),
            'tipo_usuario' => 'professor',
            'situacao' => 'ativo',
        ]);

        Professor::create([
            'user_id' => $user->getKey(),
            'nome' => 'Professor Legado',
            'cpf' => '987.654.321-00',
            'email' => 'professor.legado@example.test',
            'situacao' => 'ativo',
        ]);

        $migration = require database_path(
            'migrations/2026_08_10_025541_backfill_academic_user_cpf_credentials.php'
        );
        $migration->up();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'tipo_usuario' => 'professor',
            'cpf' => '98765432100',
        ])->assertRedirect(route('inicio'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('987.654.321-00', $user->refresh()->cpf);
        $this->assertTrue(Hash::check('98765432100', $user->password));
    }

    public function test_academic_profiles_cannot_replace_their_cpf_credential_with_a_password(): void
    {
        [$user] = $this->createStudentAccount('111.222.333-44');
        $originalPassword = $user->password;

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => '11122233344',
                'password' => 'OutraSenha123',
                'password_confirmation' => 'OutraSenha123',
            ])
            ->assertForbidden();

        $this->assertSame($originalPassword, $user->refresh()->password);
    }

    public function test_credential_backfill_handles_ambiguous_cpf_formats_without_breaking_login(): void
    {
        User::factory()->create([
            'email' => 'cpf.existente@example.test',
            'cpf' => '98765432100',
            'tipo_usuario' => 'administrador',
        ]);
        $user = User::factory()->create([
            'email' => 'professor.ambiguo@example.test',
            'cpf' => null,
            'password' => Hash::make('senha-antiga'),
            'tipo_usuario' => 'professor',
            'situacao' => 'ativo',
        ]);
        Professor::create([
            'user_id' => $user->getKey(),
            'nome' => 'Professor com CPF ambíguo',
            'cpf' => '987.654.321-00',
            'email' => 'cadastro.ambiguo@example.test',
            'situacao' => 'ativo',
        ]);

        $migration = require database_path(
            'migrations/2026_08_10_025541_backfill_academic_user_cpf_credentials.php'
        );
        $migration->up();

        $this->assertNull($user->refresh()->cpf);
        $this->assertTrue(Hash::check('98765432100', $user->password));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'tipo_usuario' => 'professor',
            'cpf' => '98765432100',
        ])->assertRedirect(route('inicio'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_new_professor_can_login_with_the_registered_email_and_cpf(): void
    {
        $administrator = User::factory()->create([
            'tipo_usuario' => 'administrador',
            'situacao' => 'ativo',
        ]);

        $this->actingAs($administrator)
            ->post(route('professores.store'), [
                'nome' => 'Docente Novo',
                'cpf' => '45678912300',
                'email' => 'docente.novo@example.test',
                'situacao' => 'ativo',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('professores.index'));

        $professor = Professor::query()->where('email', 'docente.novo@example.test')->firstOrFail();
        $professorUser = $professor->user;

        $this->assertSame('docente.novo@example.test', $professorUser->email);
        $this->assertSame('456.789.123-00', $professorUser->cpf);
        $this->assertTrue(Hash::check('45678912300', $professorUser->password));

        $this->put(route('professores.update', $professor), [
            'email' => 'docente.atualizado@example.test',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('professores.index'));

        $this->assertSame('docente.atualizado@example.test', $professorUser->refresh()->email);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => 'docente.atualizado@example.test',
            'tipo_usuario' => 'professor',
            'cpf' => '456.789.123-00',
        ])->assertRedirect(route('inicio'));

        $this->assertAuthenticatedAs($professorUser);
    }

    /** @return array{User, Professor} */
    private function createProfessorAccount(string $cpf): array
    {
        $cpfDigits = (string) preg_replace('/\D/', '', $cpf);
        $user = User::factory()->create([
            'name' => 'Professor Teste',
            'email' => 'professor@example.test',
            'cpf' => $cpf,
            'password' => Hash::make($cpfDigits),
            'tipo_usuario' => 'professor',
            'situacao' => 'ativo',
        ]);

        $professor = Professor::create([
            'user_id' => $user->getKey(),
            'nome' => 'Professor Teste',
            'cpf' => $cpf,
            'email' => 'cadastro.professor@example.test',
            'situacao' => 'ativo',
        ]);

        return [$user, $professor];
    }

    /** @return array{User, Aluno} */
    private function createStudentAccount(string $cpf, string $accountStatus = 'ativo'): array
    {
        $cpfDigits = (string) preg_replace('/\D/', '', $cpf);
        $user = User::factory()->create([
            'name' => 'Aluno Teste',
            'email' => 'aluno@example.test',
            'cpf' => $cpf,
            'password' => Hash::make($cpfDigits),
            'tipo_usuario' => 'aluno',
            'situacao' => $accountStatus,
        ]);

        $aluno = Aluno::create([
            'user_id' => $user->getKey(),
            'numero_matricula' => '2026001',
            'nome' => 'Aluno Teste',
            'cpf' => $cpf,
            'data_nascimento' => '2010-01-01',
            'email' => 'cadastro.aluno@example.test',
            'situacao' => 'ativo',
        ]);

        return [$user, $aluno];
    }
}

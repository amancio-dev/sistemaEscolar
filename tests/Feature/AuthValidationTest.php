<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_cannot_create_an_administrator(): void
    {
        $response = $this->post('/cadastrar', [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'cpf' => '123.456.789-00',
            'tipo_usuario' => 'administrador',
        ]);

        $response->assertRedirect(route('inicio'));

        $user = User::query()->where('email', 'maria@example.com')->firstOrFail();
        $this->assertSame('aluno', $user->tipo_usuario);
    }
}

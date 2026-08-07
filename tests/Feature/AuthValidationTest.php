<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_password_validation_message_is_portuguese(): void
    {
        $response = $this->from('/cadastrar')->post('/cadastrar', [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'tipo_usuario' => 'administrador',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'A senha deve conter pelo menos uma letra.',
        ]);
    }
}

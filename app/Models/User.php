<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'cpf', 'password', 'situacao', 'tipo_usuario'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function aluno(): HasOne
    {
        return $this->hasOne(Aluno::class);
    }

    public function professor(): HasOne
    {
        return $this->hasOne(Professor::class);
    }

    public function isAluno(): bool
    {
        return $this->tipo_usuario === 'aluno';
    }

    public function podeGerenciarDadosAcademicos(): bool
    {
        return in_array($this->tipo_usuario, ['administrador', 'professor'], true);
    }
}

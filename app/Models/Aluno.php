<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    //
    protected $table = 'alunos';
    protected $primaryKey = 'id_aluno';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nome',
        'numero_matricula',
        'user_id',
        'cpf',
        'data_nascimento',
        'telefone',
        'email',
        'endereco',
        'nome_responsavel',
        'telefone_responsavel',
        'situacao',
    ];

    protected function casts(): array
    {
        return ['data_nascimento' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'aluno_id', 'id_aluno');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'aluno_id', 'id_aluno');
    }

    public function frequencias(): HasMany
    {
        return $this->hasMany(Frequencia::class, 'aluno_id', 'id_aluno');
    }
}

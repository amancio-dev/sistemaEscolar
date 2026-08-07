<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Frequencia extends Model
{
    //
    protected $table = 'frequencias';
    protected $primaryKey = 'id_frequencia';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'aluno_id',
        'disciplina_id',
        'turma_id',
        'professor_id',
        'data_aula',
        'situacao',
        'justificativa',
    ];

    protected function casts(): array
    {
        return ['data_aula' => 'date'];
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id', 'id_aluno');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id', 'id_disciplina');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id', 'id_turma');
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class, 'professor_id', 'id_professor');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nota extends Model
{
    //
    protected $table = 'notas';
    protected $primaryKey = 'id_nota';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'aluno_id',
        'disciplina_id',
        'turma_id',
        'professor_id',
        'periodo',
        'avaliacao',
        'valor',
    ];

    protected function casts(): array
    {
        return ['valor' => 'decimal:2'];
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

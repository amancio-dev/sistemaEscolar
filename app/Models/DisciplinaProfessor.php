<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaProfessor extends Model
{
    protected $table = 'disciplina_professor';

    protected $primaryKey = 'id_disciplina_professor';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'professor_id',
        'disciplina_id',
        'turma_id',
    ];

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class, 'professor_id', 'id_professor');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id', 'id_disciplina');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id', 'id_turma');
    }
}

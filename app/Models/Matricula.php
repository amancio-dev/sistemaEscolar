<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Matricula extends Model
{
    //
    protected $table = 'matriculas';
    protected $primaryKey = 'id_matricula';
    public $incrementing = true;
    protected $keyType = 'int'; 

    protected $fillable = [
        'aluno_id',
        'turma_id',
        'data_matricula',
        'ano_letivo',
        'situacao',
        'observacoes',

    ];

    protected function casts(): array
    {
        return ['data_matricula' => 'date'];
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id', 'id_aluno');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id', 'id_turma');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplinaProfessor extends Model
{
    //
    protected $table = 'disciplina_professor';
    protected $primaryKey = 'id_disciplina_professor';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'professor_id',
        'disciplina_id',
        'turma_id'
    ];
}

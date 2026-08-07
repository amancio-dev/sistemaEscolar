<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turma extends Model
{
    //
    protected $table = 'turmas';
    protected $primaryKey = 'id_turma';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nome',
        'serie',
        'turno',
        'sala',
        'ano_letivo',
        'limite_alunos',
        'professor_responsavel_id',
        'situacao',
    ];

    public function professorResponsavel(): BelongsTo
    {
        return $this->belongsTo(Professor::class, 'professor_responsavel_id', 'id_professor');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'turma_id', 'id_turma');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'turma_id', 'id_turma');
    }

    public function frequencias(): HasMany
    {
        return $this->hasMany(Frequencia::class, 'turma_id', 'id_turma');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disciplina extends Model
{
    //
    protected $table = 'disciplinas';

    protected $primaryKey = 'id_disciplina';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'media_minima',
        'carga_horaria',
        'situacao',
    ];

    protected function casts(): array
    {
        return ['media_minima' => 'decimal:2'];
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'disciplina_id', 'id_disciplina');
    }

    public function frequencias(): HasMany
    {
        return $this->hasMany(Frequencia::class, 'disciplina_id', 'id_disciplina');
    }

    public function alocacoes(): HasMany
    {
        return $this->hasMany(DisciplinaProfessor::class, 'disciplina_id', 'id_disciplina');
    }
}

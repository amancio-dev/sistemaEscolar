<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Professor extends Model
{
    //
    protected $table = 'professores';
    protected $primaryKey = 'id_professor';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nome',
        'user_id',
        'cpf',
        'data_contratacao',
        'telefone',
        'email',
        'endereco',
        'formacao',
        'especialidade',
        'situacao',
    ];

    protected function casts(): array
    {
        return ['data_contratacao' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turmasResponsavel(): HasMany
    {
        return $this->hasMany(Turma::class, 'professor_responsavel_id', 'id_professor');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'professor_id', 'id_professor');
    }

    public function frequencias(): HasMany
    {
        return $this->hasMany(Frequencia::class, 'professor_id', 'id_professor');
    }
}

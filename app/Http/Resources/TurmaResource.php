<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TurmaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'nome' => $this->nome,
            'serie' => $this->serie,
            'turno' => $this->turno,
            'sala' => $this->sala,
            'ano_letivo' => (int) $this->ano_letivo,
            'limite_alunos' => (int) $this->limite_alunos,
            'situacao' => $this->situacao,
            'professor_responsavel' => $this->professorResponsavel ? [
                'id' => $this->professorResponsavel->getKey(),
                'nome' => $this->professorResponsavel->nome,
            ] : null,
            'alocacoes' => $this->whenLoaded('alocacoes', fn () => $this->alocacoes->map(fn ($alocacao): array => [
                'id' => $alocacao->getKey(),
                'disciplina' => [
                    'id' => $alocacao->disciplina?->getKey(),
                    'nome' => $alocacao->disciplina?->nome,
                    'codigo' => $alocacao->disciplina?->codigo,
                ],
                'professor' => [
                    'id' => $alocacao->professor?->getKey(),
                    'nome' => $alocacao->professor?->nome,
                ],
            ])->values()),
            'matriculas' => $this->whenLoaded('matriculas', fn () => $this->matriculas->map(fn ($matricula): array => [
                'id' => $matricula->getKey(),
                'data_matricula' => $matricula->data_matricula?->toDateString(),
                'aluno' => [
                    'id' => $matricula->aluno?->getKey(),
                    'nome' => $matricula->aluno?->nome,
                    'numero_matricula' => $matricula->aluno?->numero_matricula,
                ],
            ])->values()),
        ];
    }
}

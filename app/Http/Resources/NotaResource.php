<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'periodo' => $this->periodo,
            'avaliacao' => $this->avaliacao,
            'valor' => (float) $this->valor,
            'aluno' => $this->whenLoaded('aluno', fn (): ?array => $this->aluno ? [
                'id' => $this->aluno->getKey(),
                'nome' => $this->aluno->nome,
                'numero_matricula' => $this->aluno->numero_matricula,
            ] : null),
            'disciplina' => $this->whenLoaded('disciplina', fn (): ?array => $this->disciplina ? [
                'id' => $this->disciplina->getKey(),
                'nome' => $this->disciplina->nome,
                'codigo' => $this->disciplina->codigo,
            ] : null),
            'turma' => $this->whenLoaded('turma', fn (): ?array => $this->turma ? [
                'id' => $this->turma->getKey(),
                'nome' => $this->turma->nome,
                'serie' => $this->turma->serie,
                'ano_letivo' => (int) $this->turma->ano_letivo,
            ] : null),
            'professor' => $this->whenLoaded('professor', fn (): ?array => $this->professor ? [
                'id' => $this->professor->getKey(),
                'nome' => $this->professor->nome,
            ] : null),
        ];
    }
}

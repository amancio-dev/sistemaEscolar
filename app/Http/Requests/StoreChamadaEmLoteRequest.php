<?php

namespace App\Http\Requests;

use App\Models\DisciplinaProfessor;
use App\Models\Matricula;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreChamadaEmLoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->podeGerenciarDadosAcademicos() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'alocacao_id' => ['required', 'integer', 'exists:disciplina_professor,id_disciplina_professor'],
            'data_aula' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'frequencias' => ['required', 'array', 'min:1'],
            'frequencias.*.aluno_id' => ['required', 'integer', 'distinct', 'exists:alunos,id_aluno'],
            'frequencias.*.situacao' => [
                'required',
                Rule::in(['presente', 'ausente', 'justificada', 'atrasado']),
            ],
            'frequencias.*.justificativa' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['alocacao_id', 'frequencias'])) {
                return;
            }

            $alocacao = DisciplinaProfessor::query()
                ->with(['professor', 'disciplina', 'turma'])
                ->find($this->integer('alocacao_id'));

            if (! $alocacao) {
                return;
            }

            $user = $this->user();

            if ($user?->tipo_usuario === 'professor'
                && (int) $alocacao->professor?->user_id !== (int) $user->getKey()) {
                $validator->errors()->add('alocacao_id', 'Você só pode registrar chamadas nas suas próprias alocações.');
            }

            if ($alocacao->professor?->situacao !== 'ativo'
                || $alocacao->disciplina?->situacao !== 'ativa'
                || $alocacao->turma?->situacao !== 'ativa') {
                $validator->errors()->add('alocacao_id', 'A chamada exige professor, disciplina e turma ativos.');
            }

            $matriculados = Matricula::query()
                ->where('turma_id', $alocacao->turma_id)
                ->where('situacao', 'ativa')
                ->whereHas('aluno', fn ($query) => $query->where('situacao', 'ativo'))
                ->pluck('aluno_id')
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values();

            $enviados = collect($this->input('frequencias', []))
                ->pluck('aluno_id')
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values();

            if ($matriculados->all() !== $enviados->all()) {
                $validator->errors()->add(
                    'frequencias',
                    'A chamada deve conter exatamente os alunos com matrícula ativa nesta turma.',
                );
            }

            foreach ($this->input('frequencias', []) as $index => $frequencia) {
                if (($frequencia['situacao'] ?? null) === 'justificada'
                    && blank($frequencia['justificativa'] ?? null)) {
                    $validator->errors()->add(
                        "frequencias.{$index}.justificativa",
                        'Informe a justificativa desta falta.',
                    );
                }
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'data_aula.before_or_equal' => 'A data da aula não pode estar no futuro.',
            'frequencias.required' => 'Não há alunos informados para esta chamada.',
            'frequencias.*.aluno_id.distinct' => 'Um aluno não pode aparecer duas vezes na mesma chamada.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'alocacao_id' => 'alocação docente',
            'data_aula' => 'data da aula',
            'frequencias' => 'lista de frequência',
            'frequencias.*.aluno_id' => 'aluno',
            'frequencias.*.situacao' => 'situação',
            'frequencias.*.justificativa' => 'justificativa',
        ];
    }
}

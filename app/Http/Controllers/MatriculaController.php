<?php

namespace App\Http\Controllers;

use App\Actions\SalvarMatricula;
use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Turma;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MatriculaController extends CrudController
{
    public function __construct(private readonly SalvarMatricula $salvarMatricula) {}

    protected string $modelClass = Matricula::class;

    protected string $primaryKey = 'id_matricula';

    protected array $searchable = ['id_matricula', 'ano_letivo', 'situacao'];

    protected string $singularLabel = 'registro de matrícula';

    protected string $pluralLabel = 'Registros de matrículas';

    protected string $resource = 'matriculas';

    protected array $with = ['aluno', 'turma'];

    protected array $relationSearches = [
        'aluno' => ['nome', 'numero_matricula'],
        'turma' => ['nome', 'serie'],
    ];

    protected function rules(?int $id = null): array
    {
        $prefix = $id === null ? 'required' : 'sometimes';

        return [
            'aluno_id' => [$prefix, 'integer', 'exists:alunos,id_aluno'],
            'turma_id' => [$prefix, 'integer', 'exists:turmas,id_turma'],
            'data_matricula' => [$prefix, 'date'],
            'ano_letivo' => [$prefix, 'digits:4'],
            'situacao' => [$prefix, Rule::in(['ativa', 'trancada', 'cancelada', 'transferida', 'concluida'])],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());
        $matricula = $this->salvarMatricula->execute($validated);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => "{$this->singularLabel} cadastrado com sucesso.",
                'data' => $matricula,
            ], 201);
        }

        return redirect()
            ->route("{$this->resource}.index")
            ->with('success', ucfirst($this->singularLabel).' cadastrado com sucesso.');
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate($this->rules($id), $this->messages(), $this->attributes());
        $matricula = $this->salvarMatricula->execute($validated, $id);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => "{$this->singularLabel} atualizado com sucesso.",
                'data' => $matricula,
            ]);
        }

        return redirect()
            ->route("{$this->resource}.index")
            ->with('success', ucfirst($this->singularLabel).' atualizado com sucesso.');
    }

    protected function formData(): array
    {
        return [
            'alunos' => Aluno::query()
                ->select(['id_aluno', 'nome', 'numero_matricula'])
                ->orderBy('nome')
                ->get(),
            'turmas' => Turma::query()
                ->select(['id_turma', 'nome', 'serie', 'ano_letivo', 'limite_alunos', 'situacao'])
                ->withCount([
                    'matriculas as matriculas_ativas_count' => fn ($query) => $query->where('situacao', 'ativa'),
                ])
                ->orderBy('nome')
                ->get(),
        ];
    }

    protected function messages(): array
    {
        return [
            'aluno_id.required' => 'Selecione o aluno da matrícula.',
            'aluno_id.exists' => 'O aluno selecionado não foi encontrado.',
            'turma_id.required' => 'Selecione a turma da matrícula.',
            'turma_id.exists' => 'A turma selecionada não foi encontrada.',
            'data_matricula.required' => 'Informe a data da matrícula.',
            'ano_letivo.required' => 'Informe o ano letivo da matrícula.',
            'ano_letivo.digits' => 'Informe o ano letivo com quatro dígitos.',
            'situacao.required' => 'Selecione a situação da matrícula.',
            'situacao.in' => 'Selecione uma situação de matrícula válida.',
        ];
    }

    protected function attributes(): array
    {
        return [
            'aluno_id' => 'aluno',
            'turma_id' => 'turma',
            'data_matricula' => 'data da matrícula',
            'ano_letivo' => 'ano letivo',
            'situacao' => 'situação',
            'observacoes' => 'observações',
        ];
    }
}
